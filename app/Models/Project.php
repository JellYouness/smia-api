<?php

namespace App\Models;

use App\Enums\PROJECT_STATUS;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Project extends Model
{
  use HasFactory;

  protected $fillable = [
    'title',
    'description',
    'status',
    'start_date',
    'end_date',
    'budget',
    'client_id',
    'creator_id',
    'ambassador_id',
  ];

  protected $casts = [
    'start_date' => 'datetime',
    'end_date' => 'datetime',
  ];

  protected static function booted()
  {
    parent::booted();
    static::created(function ($project) {
      // Give permissions to the client user if client_id is set
      if ($project->client_id) {
        $client = $project->client;
        if ($client && $client->user) {
          $user = $client->user;
          $user->givePermission('projects.' . $project->id . '.read');
          $user->givePermission('projects.' . $project->id . '.update');
          $user->givePermission('projects.' . $project->id . '.delete');
        }
      }
      // Give permissions to the ambassador user if ambassador_id is set
      if ($project->ambassador_id) {
        $ambassador = $project->ambassador;
        if ($ambassador && $ambassador->user) {
          $user = $ambassador->user;
          $user->givePermission('projects.' . $project->id . '.read');
          $user->givePermission('projects.' . $project->id . '.update');
          $user->givePermission('projects.' . $project->id . '.delete');
        }
      }
    });

    static::updated(function ($project) {
      // Handle client_id changes
      $originalClientId = $project->getOriginal('client_id');
      if ($originalClientId && $originalClientId != $project->client_id) {
        $oldClient = Client::find($originalClientId);
        if ($oldClient && $oldClient->user) {
          $oldClient->user->removeAllPermissions('projects', $project->id);
        }
      }
      if ($project->client_id && $originalClientId != $project->client_id) {
        $newClient = $project->client;
        if ($newClient && $newClient->user) {
          $newClient->user->givePermission('projects.' . $project->id . '.read');
          $newClient->user->givePermission('projects.' . $project->id . '.update');
          $newClient->user->givePermission('projects.' . $project->id . '.delete');
        }
      }
      // Handle ambassador_id changes
      $originalAmbassadorId = $project->getOriginal('ambassador_id');
      if ($originalAmbassadorId && $originalAmbassadorId != $project->ambassador_id) {
        $oldAmbassador = Ambassador::find($originalAmbassadorId);
        if ($oldAmbassador && $oldAmbassador->user) {
          $oldAmbassador->user->removeAllPermissions('projects', $project->id);
        }
      }
      if ($project->ambassador_id && $originalAmbassadorId != $project->ambassador_id) {
        $newAmbassador = $project->ambassador;
        if ($newAmbassador && $newAmbassador->user) {
          $newAmbassador->user->givePermission('projects.' . $project->id . '.read');
          $newAmbassador->user->givePermission('projects.' . $project->id . '.update');
          $newAmbassador->user->givePermission('projects.' . $project->id . '.delete');
        }
      }
    });

    static::deleted(function ($project) {
      // Remove permissions from the client user if client_id is set
      if ($project->client_id) {
        $client = $project->client;
        if ($client && $client->user) {
          $user = $client->user;
          $user->removeAllPermissions('projects', $project->id);
        }
      }
      // Remove permissions from the ambassador user if ambassador_id is set
      if ($project->ambassador_id) {
        $ambassador = $project->ambassador;
        if ($ambassador && $ambassador->user) {
          $user = $ambassador->user;
          $user->removeAllPermissions('projects', $project->id);
        }
      }
    });
  }

  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(Creator::class);
  }

  public function ambassador(): BelongsTo
  {
    return $this->belongsTo(Ambassador::class);
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'title' => "$required|string|max:255",
      'description' => "$required|string",
      'status' => ['nullable', 'string', Rule::in(array_column(PROJECT_STATUS::cases(), 'value'))],
      'start_date' => "$required|date",
      'end_date' => "$required|date|after_or_equal:start_date",
      'budget' => "$required|numeric|min:0",
      'client_id' => 'nullable|exists:clients,id',
      'creator_id' => 'nullable|exists:creators,id',
      'ambassador_id' => 'nullable|exists:ambassadors,id',
    ];
  }
}
