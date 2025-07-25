<?php

namespace App\Models;

use App\Enums\CREATOR_PROJECT_PERMISSION;
use App\Enums\CREATOR_PROJECT_STATUS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class ProjectCreator extends Model
{
  use HasFactory;

  protected $fillable = ['project_id', 'creator_id', 'role', 'status', 'permission'];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(Creator::class);
  }

  public static function booted()
  {
    parent::booted();
    static::created(function ($projectCreator) {
      // On assigning a new creator, give permissions based on their permission level
      \Log::info('Creating project creator', ['projectCreator' => $projectCreator]);
      if ($projectCreator->creator && $projectCreator->creator->user && $projectCreator->project) {
        $user = $projectCreator->creator->user;
        $project = $projectCreator->project;
        // Grant project read permission
        \Log::info('Assigning project permissions to user', ['user' => $user]);
        $user->givePermission('projects.' . $project->id . '.read');
        $mediaPosts = $project->mediaPosts ?? $project->media_posts ?? [];
        if (empty($mediaPosts) && method_exists($project, 'mediaPosts')) {
          $mediaPosts = $project->mediaPosts()->get();
        }
        if ($projectCreator->permission === CREATOR_PROJECT_PERMISSION::EDITOR->value) {
          foreach ($mediaPosts as $mediaPost) {
            $user->givePermission('media_posts.' . $mediaPost->id . '.read');
            $user->givePermission('media_posts.' . $mediaPost->id . '.update');
          }
        } elseif ($projectCreator->permission === CREATOR_PROJECT_PERMISSION::VIEWER->value) {
          foreach ($mediaPosts as $mediaPost) {
            $user->givePermission('media_posts.' . $mediaPost->id . '.read');
          }
        }
        // If permission is null, do not give any media post permissions
      }
    });
    static::updated(function ($projectCreator) {
      // On updating a creator, adjust permissions based on their permission level
      if ($projectCreator->creator && $projectCreator->creator->user && $projectCreator->project) {
        $user = $projectCreator->creator->user;
        $project = $projectCreator->project;
        $mediaPosts = $project->mediaPosts ?? $project->media_posts ?? [];
        if (empty($mediaPosts) && method_exists($project, 'mediaPosts')) {
          $mediaPosts = $project->mediaPosts()->get();
        }
        // Remove all previous media post permissions
        foreach ($mediaPosts as $mediaPost) {
          $user->removeAllPermissions('media_posts', $mediaPost->id);
        }
        // Grant new permissions based on updated permission
        if ($projectCreator->permission === CREATOR_PROJECT_PERMISSION::EDITOR->value) {
          foreach ($mediaPosts as $mediaPost) {
            $user->givePermission('media_posts.' . $mediaPost->id . '.read');
            $user->givePermission('media_posts.' . $mediaPost->id . '.update');
          }
        } elseif ($projectCreator->permission === CREATOR_PROJECT_PERMISSION::VIEWER->value) {
          foreach ($mediaPosts as $mediaPost) {
            $user->givePermission('media_posts.' . $mediaPost->id . '.read');
          }
        }
        // If permission is null, do not give any media post permissions
      }
    });
    static::deleted(function ($projectCreator) {
      // On deleting a creator, remove all permissions for media posts of the project and project read
      if ($projectCreator->creator && $projectCreator->creator->user && $projectCreator->project) {
        $user = $projectCreator->creator->user;
        $project = $projectCreator->project;
        $user->removeAllPermissions('projects', $project->id);
        $mediaPosts = $project->mediaPosts ?? $project->media_posts ?? [];
        if (empty($mediaPosts) && method_exists($project, 'mediaPosts')) {
          $mediaPosts = $project->mediaPosts()->get();
        }
        foreach ($mediaPosts as $mediaPost) {
          $user->removeAllPermissions('media_posts', $mediaPost->id);
        }
      }
    });
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'project_id' => "$required|exists:projects,id",
      'creator_id' => "$required|exists:creators,id",
      'role' => 'nullable|string',
      'status' => ['nullable', 'string', Rule::in(array_column(CREATOR_PROJECT_STATUS::cases(), 'value'))],
      'permission' => ['nullable', 'string', Rule::in(array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value'))],
    ];
  }
}
