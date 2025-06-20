<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_size',
        'industry',
        'website_url',
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'tax_identifier',
        'budget',
        'preferred_creators',
        'project_count',
        'default_project_settings',
    ];

    protected $casts = [
        'preferred_creators' => 'array',
        'default_project_settings' => 'array',
    ];

    /**
     * Get the user that owns the client.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the projects for the client.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
