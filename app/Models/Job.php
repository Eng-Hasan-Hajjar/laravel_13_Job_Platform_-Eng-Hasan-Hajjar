<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, BelongsToMany};
use Illuminate\Support\Str;

class Job extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'title', 'slug',
        'description', 'requirements', 'benefits',
        'type', 'location', 'is_remote',
        'salary_min', 'salary_max', 'salary_currency', 'salary_period',
        'experience_level', 'skills', 'deadline',
        'is_active', 'is_featured', 'views_count', 'applications_count',
    ];

    // ── الحل الأساسي: تحويل كل حقول JSON إلى array تلقائياً ──────
    protected $casts = [
        'skills'          => 'array',   // ← هذا هو سبب الخطأ إذا كان ناقصاً
        'is_remote'       => 'boolean',
        'is_active'       => 'boolean',
        'is_featured'     => 'boolean',
        'deadline'        => 'datetime',
        'salary_min'      => 'decimal:2',
        'salary_max'      => 'decimal:2',
        'views_count'     => 'integer',
        'applications_count' => 'integer',
    ];

    // ── Route Model Binding بالـ slug ──────────────────────────────
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ══════════════════════════════════════════════════════════════
    //  العلاقات
    // ══════════════════════════════════════════════════════════════

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'saved_jobs');
    }

    // ══════════════════════════════════════════════════════════════
    //  Scopes
    // ══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('deadline')
                                         ->orWhere('deadline', '>=', now()));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInLocation($query, string $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeWithSalaryBetween($query, $min, $max)
    {
        return $query->when($min, fn($q) => $q->where('salary_max', '>=', $min))
                     ->when($max, fn($q) => $q->where('salary_min', '<=', $max));
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(fn($q) => $q
            ->where('title',       'like', "%{$term}%")
            ->orWhere('description','like', "%{$term}%")
            ->orWhere('location',   'like', "%{$term}%")
        );
    }

    public function scopePostedWithin($query, int $days)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ══════════════════════════════════════════════════════════════
    //  Helpers
    // ══════════════════════════════════════════════════════════════

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /** تأكد دائماً من إرجاع مصفوفة حتى لو القيمة null أو string */
    public function getSkillsAttribute($value): array
    {
        if (is_array($value)) return $value;
        if (empty($value))    return [];

        // إذا كان JSON string
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // إذا كان نص عادي مفصول بفاصلة
        return array_map('trim', explode(',', $value));
    }

    // توليد slug فريد
    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'like', "{$slug}%")->count();
        return $count > 0 ? "{$slug}-" . Str::random(5) : $slug;
    }
}