<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamMemberInvitation extends BaseModel
{
    use HasFactory;

    public static $cacheKey = 'team_member_invitations';

    protected $fillable = [
        'ambassador_id',
        'user_id',
        'role',
        'is_primary',
        'status',
        'token',
        'expires_at',
        'accepted_at',
        'declined_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    /**
     * Get the ambassador that sent the invitation.
     */
    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(Ambassador::class);
    }

    /**
     * Get the user that was invited.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the invitation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation can be accepted.
     */
    public function canBeAccepted(): bool
    {
        return $this->status === 'PENDING' && !$this->isExpired();
    }

    /**
     * Accept the invitation.
     */
    public function accept(): bool
    {
        if (!$this->canBeAccepted()) {
            return false;
        }

        $this->update([
            'status' => 'ACCEPTED',
            'accepted_at' => now(),
        ]);

        return true;
    }

    /**
     * Decline the invitation.
     */
    public function decline(): bool
    {
        if (!$this->canBeAccepted()) {
            return false;
        }

        $this->update([
            'status' => 'DECLINED',
            'declined_at' => now(),
        ]);

        return true;
    }

    /**
     * Generate a unique token for the invitation.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Find invitation by token.
     */
    public static function findByToken(string $token): ?self
    {
        return static::where('token', $token)->first();
    }

    /**
     * Validation rules for creating or updating a TeamMemberInvitation.
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
            'status' => 'nullable|in:PENDING,ACCEPTED,DECLINED,EXPIRED',
            'token' => 'nullable|string|unique:team_member_invitations,token,' . $id,
            'expires_at' => 'nullable|date|after:now',
        ];
    }
}
