<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAdministrator extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'role',
    'permissions',
    'last_login',
    'access_level',
    'department',
    'supervisor_id',
    'is_active',
  ];

  protected $casts = [
    'permissions' => 'array',
    'last_login' => 'datetime',
    'is_active' => 'boolean',
  ];

  /**
   * Get the user that owns the system administrator.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the supervisor of the system administrator.
   */
  public function supervisor(): BelongsTo
  {
    return $this->belongsTo(SystemAdministrator::class, 'supervisor_id');
  }

  /**
   * Validation rules for creating or updating a SystemAdministrator.
   * If $id is provided, use 'sometimes|required' for update context.
   */
  public static function rules($id = null): array
  {
    $id = $id ?? request()->route('id');
    $required = $id ? 'sometimes|required' : 'required';
    return [
      'user_id' => $id ? 'sometimes|exists:users,id' : 'required|exists:users,id',
      'role' => "$required|string|max:255",
      'permissions' => "$required|array",
      'access_level' => "$required|in:BASIC,STANDARD,ADVANCED,SUPER_ADMIN",
      'department' => "$required|string|max:255",
      'supervisor_id' => 'nullable|exists:system_administrators,id',
      'is_active' => 'boolean',
    ];
  }
}
