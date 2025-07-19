<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCreator extends Model
{
  use HasFactory;

  protected $fillable = ['project_id', 'creator_id', 'role', 'status'];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(Creator::class);
  }

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'project_id' => "$required|exists:projects,id",
      'creator_id' => "$required|exists:creators,id",
      'role' => 'nullable|string',
      'status' => 'nullable|string',
    ];
  }
}
