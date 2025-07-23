<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'token_hash',
        'device',
        'ip_address',
        'user_agent',
        'last_active',
    ];

    protected $casts = [
        'last_active' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this session is the current one
     */
    public function isCurrentSession(): bool
    {
        return $this->token_hash === hash('sha256', request()->bearerToken());
    }

    /**
     * Get formatted last active time
     */
    public function getFormattedLastActiveAttribute(): string
    {
        return $this->last_active ? $this->last_active->diffForHumans() : 'Never';
    }
}
