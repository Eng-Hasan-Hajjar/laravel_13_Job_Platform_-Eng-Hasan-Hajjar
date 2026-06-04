<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'slug', 'description', 'industry',
        'location', 'website', 'email', 'phone',
        'logo', 'cover_image',
        'employees_count', 'founded_year',
        'linkedin', 'twitter', 'facebook',
        'is_verified', 'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active'   => 'boolean',
        'founded_year'=> 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ══════════════════════════════════════════════════════════════
    //  العلاقات
    // ══════════════════════════════════════════════════════════════

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CompanyReview::class);
    }

    // ══════════════════════════════════════════════════════════════
    //  Scopes
    // ══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // ══════════════════════════════════════════════════════════════
    //  Computed Attributes
    // ══════════════════════════════════════════════════════════════

    public function getAverageRatingAttribute(): float
    {
        return (float) $this->reviews()->where('is_approved', true)->avg('rating') ?? 0.0;
    }

    public function getActiveJobsCountAttribute(): int
    {
        return $this->jobs()->active()->count();
    }

    public function activeJobs()
    {
        return $this->jobs()->active();
    }
}