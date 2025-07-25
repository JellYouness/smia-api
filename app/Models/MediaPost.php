<?php

namespace App\Models;

use App\Enums\MEDIA_POST_STATUS;
use App\Enums\MEDIA_POST_PRIORITY;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rule;

class MediaPost extends BaseModel
{
  use HasFactory;

  protected $fillable = [
    'project_id',
    'title',
    'description',
    'status',
    'priority',
    'position',
    'due_date',
  ];

  protected $casts = [
    'due_date' => 'date',
  ];

  protected $with = [
    'assignments',
    'assets',
    'reviews',
    'comments',
    'project',
  ];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function assignments(): HasMany
  {
    return $this->hasMany(MediaPostAssignment::class, 'post_id');
  }

  public function assets(): HasMany
  {
    return $this->hasMany(MediaPostAsset::class, 'post_id');
  }

  public function versions(): HasMany
  {
    return $this->hasMany(MediaPostVersion::class, 'post_id');
  }

  public function draftFiles(): HasMany
  {
    return $this->hasMany(MediaPostAsset::class, 'post_id')
      ->whereNull('version_id')
      ->where('is_reference', false);
  }

  public function referenceAssets(): HasMany
  {
    return $this->hasMany(MediaPostAsset::class, 'post_id')
      ->where('is_reference', true);
  }

  public function reviews(): HasMany
  {
    return $this->hasMany(MediaPostReview::class, 'post_id');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(MediaPostComment::class, 'post_id');
  }


  public static function booted()
  {
    parent::booted();
    static::created(function ($mediaPost) {
      // Grant permissions to the client user of the associated project
      if ($mediaPost->project && $mediaPost->project->client && $mediaPost->project->client->user) {
        $user = $mediaPost->project->client->user;
        $user->givePermission('media_posts.' . $mediaPost->id . '.read');
        $user->givePermission('media_posts.' . $mediaPost->id . '.update');
        $user->givePermission('media_posts.' . $mediaPost->id . '.delete');
      }
      // Grant permissions to all creator users of the associated project (via creators relation)
      if ($mediaPost->project && $mediaPost->project->creators) {
        foreach ($mediaPost->project->creators as $projectCreator) {
          if ($projectCreator->creator && $projectCreator->creator->user) {
            $creatorUser = $projectCreator->creator->user;
            $creatorUser->givePermission('media_posts.' . $mediaPost->id . '.read');
            $creatorUser->givePermission('media_posts.' . $mediaPost->id . '.update');
          }
        }
      }
    });

    static::updated(function ($mediaPost) {
      // Handle project_id changes
      $originalProjectId = $mediaPost->getOriginal('project_id');
      if ($originalProjectId && $originalProjectId != $mediaPost->project_id) {
        $oldProject = Project::find($originalProjectId);
        if ($oldProject && $oldProject->client && $oldProject->client->user) {
          $oldProject->client->user->removeAllPermissions('media_posts', $mediaPost->id);
        }
        if ($oldProject && $oldProject->creators) {
          foreach ($oldProject->creators as $projectCreator) {
            if ($projectCreator->creator && $projectCreator->creator->user) {
              $creatorUser = $projectCreator->creator->user;
              $creatorUser->removeAllPermissions('media_posts', $mediaPost->id);
            }
          }
        }
      }
      if ($mediaPost->project_id && $originalProjectId != $mediaPost->project_id) {
        $newProject = $mediaPost->project;
        if ($newProject && $newProject->client && $newProject->client->user) {
          $newProject->client->user->givePermission('media_posts.' . $mediaPost->id . '.read');
          $newProject->client->user->givePermission('media_posts.' . $mediaPost->id . '.update');
          $newProject->client->user->givePermission('media_posts.' . $mediaPost->id . '.delete');
        }
        if ($newProject && $newProject->creators) {
          foreach ($newProject->creators as $projectCreator) {
            if ($projectCreator->creator && $projectCreator->creator->user) {
              $creatorUser = $projectCreator->creator->user;
              $creatorUser->givePermission('media_posts.' . $mediaPost->id . '.read');
              $creatorUser->givePermission('media_posts.' . $mediaPost->id . '.update');
            }
          }
        }
      }
    });

    static::deleted(function ($mediaPost) {
      // Remove permissions from the client and all creator users if project_id is set
      if ($mediaPost->project_id) {
        $project = $mediaPost->project;
        if ($project && $project->client && $project->client->user) {
          $user = $project->client->user;
          $user->removeAllPermissions('media_posts', $mediaPost->id);
        }
        if ($project && $project->creators) {
          foreach ($project->creators as $projectCreator) {
            if ($projectCreator->creator && $projectCreator->creator->user) {
              $creatorUser = $projectCreator->creator->user;
              $creatorUser->removeAllPermissions('media_posts', $mediaPost->id);
            }
          }
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
      'title' => 'nullable|string|max:255',
      'description' => 'nullable|string',
      'status' => ['nullable', 'string', Rule::in(array_column(MEDIA_POST_STATUS::cases(), 'value'))],
      'priority' => ['nullable', 'string', Rule::in(array_column(MEDIA_POST_PRIORITY::cases(), 'value'))],
      'position' => 'nullable|integer',
      'due_date' => 'nullable|date',
    ];
  }
}
