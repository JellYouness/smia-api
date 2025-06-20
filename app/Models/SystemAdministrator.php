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
}
