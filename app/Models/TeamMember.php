<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamMember extends BaseModel
{
    use HasFactory;

    public static $cacheKey = 'team_members';

    protected $table = 'ambassador_team_members';

    protected $fillable = [
        'ambassador_id',
        'user_id',
        'role',
        'is_primary',
        'joined_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'joined_at' => 'datetime',
    ];

    /**
     * Get the ambassador that owns the team member.
     */
    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(Ambassador::class);
    }

    /**
     * Get the user that is the team member.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Validation rules for creating or updating a TeamMember.
     */
    public static function rules($id = null): array
    {
        $id = $id ?? request()->route('id');
        $required = $id ? 'sometimes|required' : 'required';

        return [
            'ambassador_id' => $id ? 'sometimes|exists:ambassadors,id' : 'required|exists:ambassadors,id',
            'user_id' => $id ? 'sometimes|exists:users,id' : 'required|exists:users,id',
            'role' => 'nullable|string|max:255',
            'is_primary' => 'nullable|boolean',
            'joined_at' => 'nullable|date',
        ];
    }
}
