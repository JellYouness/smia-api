<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemAdministrator extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_level',
        'admin_permissions',
        'departments',
        'audit_log',
        'last_permission_update',
        'restricted_ip_access',
        'allowed_ip_addresses',
        'emergency_contact',
        'security_clearance',
    ];

    protected $casts = [
        'admin_permissions' => 'array',
        'departments' => 'array',
        'audit_log' => 'boolean',
        'last_permission_update' => 'datetime',
        'restricted_ip_access' => 'boolean',
        'allowed_ip_addresses' => 'array',
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
