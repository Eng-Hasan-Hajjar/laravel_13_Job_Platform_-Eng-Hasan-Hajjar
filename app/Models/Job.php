<?php
// app/Models/Job.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'category_id', 'title', 'slug',
        'description', 'requirements', 'benefits',
        'type', 'location', 'is_remote',
        'salary_min', 'salary_max', 'salary_currency', 'salary_period',
        'experience_level', 'skills', 'deadline',
        'is_active', 'is_featured', 'views_count', 'applications_count',
    ];

    protected $casts = [
        'skills' => 'array',
        'is_remote' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'deadline' => 'datetime',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    // ---- RELATIONSHIPS ----

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedByUsers()
    {
        return $this->belongsToMany(User::class, 'saved_jobs')->withTimestamps();
    }

    // ---- SCOPES ----

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(fn($q) => $q->whereNull('deadline')->orWhere('deadline', '>=', now()));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType($query, $type)
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeInLocation($query, $location)
    {
        return $location ? $query->where('location', 'like', "%{$location}%") : $query;
    }

    public function scopeWithSalaryBetween($query, $min, $max)
    {
        if ($min) $query->where('salary_max', '>=', $min);
        if ($max) $query->where('salary_min', '<=', $max);
        return $query;
    }

    public function scopeSearch($query, $term)
    {
        return $term
            ? $query->where(fn($q) => $q
                ->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('company', fn($q) => $q->where('name', 'like', "%{$term}%"))
                ->orWhereJsonContains('skills', $term))
            : $query;
    }

    public function scopePostedWithin($query, $period)
    {
        return match($period) {
            'today' => $query->whereDate('created_at', today()),
            'week'  => $query->where('created_at', '>=', now()->subWeek()),
            'month' => $query->where('created_at', '>=', now()->subMonth()),
            default => $query,
        };
    }

    // ---- HELPERS ----

    public function isExpired(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}