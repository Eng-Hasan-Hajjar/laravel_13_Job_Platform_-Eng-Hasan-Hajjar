<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany, BelongsToMany};

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'bio', 'phone', 'location', 'avatar',
        'experience_level',
        'skills',
        'preferred_job_types',
        'preferred_locations',
        'expected_salary',
        'cv_path', 'cv_analyzed',
        'locale', 'is_active', 'last_seen_at',
        'notification_preferences',
    ];

    protected $hidden = ['password', 'remember_token'];

    // ── الحل: جميع حقول JSON مُعرَّفة صراحةً ──────────────────────
    protected $casts = [
        'email_verified_at'        => 'datetime',
        'last_seen_at'             => 'datetime',
        'password'                 => 'hashed',
        'is_active'                => 'boolean',

        // ← هذه الثلاثة هي مصدر أخطاء in_array()
        'skills'                   => 'array',
        'preferred_job_types'      => 'array',
        'preferred_locations'      => 'array',

        'cv_analyzed'              => 'array',
        'notification_preferences' => 'array',
        'expected_salary'          => 'decimal:2',
    ];

    // ══════════════════════════════════════════════════════════════
    //  Accessors — ضمان إرجاع array دائماً حتى لو البيانات قديمة
    // ══════════════════════════════════════════════════════════════

    public function getSkillsAttribute($value): array
    {
        return $this->decodeJsonField($value);
    }

    public function getPreferredJobTypesAttribute($value): array
    {
        return $this->decodeJsonField($value);
    }

    public function getPreferredLocationsAttribute($value): array
    {
        return $this->decodeJsonField($value);
    }

    public function getCvAnalyzedAttribute($value): ?array
    {
        if (empty($value)) return null;
        if (is_array($value)) return $value;
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /** Helper: يحوّل أي قيمة إلى array بأمان */
    private function decodeJsonField($value): array
    {
        if (is_array($value))  return $value;
        if (empty($value))     return [];

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return array_filter(array_map('trim', explode(',', (string) $value)));
    }

    // ══════════════════════════════════════════════════════════════
    //  العلاقات
    // ══════════════════════════════════════════════════════════════

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedJobs(): BelongsToMany
    {
        return $this->belongsToMany(Job::class, 'saved_jobs');
    }

    // ══════════════════════════════════════════════════════════════
    //  Role Helpers
    // ══════════════════════════════════════════════════════════════

    public function isAdmin():   bool { return $this->role === 'admin'; }
    public function isCompany(): bool { return $this->role === 'company'; }
    public function isUser():    bool { return $this->role === 'user'; }

    // ══════════════════════════════════════════════════════════════
    //  Job Helpers
    // ══════════════════════════════════════════════════════════════

    public function hasAppliedTo(Job $job): bool
    {
        return $this->jobApplications()
                    ->where('job_id', $job->id)
                    ->exists();
    }

    public function hasSaved(Job $job): bool
    {
        return $this->savedJobs()->where('job_id', $job->id)->exists();
    }

    /** رابط الصورة الشخصية */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : null;
    }

    /** توصيات الوظائف عبر خدمة AI */
    public function getRecommendedJobs(int $limit = 10)
    {
        return app(\App\Services\AI\JobRecommendationService::class)
            ->recommend($this, $limit, true);
    }
}