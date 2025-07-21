<?php

namespace App\Models;

use App\Enums\CREATOR_PROJECT_PERMISSION;
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

  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'project_id' => "$required|exists:projects,id",
      'creator_id' => "$required|exists:creators,id",
      'role' => 'nullable|string',
      'status' => 'nullable|string',
      'permission' => ['nullable', 'string', Rule::in(array_column(CREATOR_PROJECT_PERMISSION::cases(), 'value'))],
    ];
  }
}
