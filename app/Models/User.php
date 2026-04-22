<?php
// ==========================================
// app/Models/User.php
// ==========================================
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'avatar', 'phone', 'bio', 'location',
        'cv_path', 'cv_analyzed', 'skills',
        'experience_level', 'preferred_job_types',
        'preferred_locations', 'expected_salary',
        'is_active', 'locale', 'last_seen_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'skills' => 'array',
        'preferred_job_types' => 'array',
        'preferred_locations' => 'array',
        'cv_analyzed' => 'array',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    // ---- RELATIONSHIPS ----

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function jobApplications()
    {
        return $this->hasMany(JobApplication::class);
    }

    public function savedJobs()
    {
        return $this->belongsToMany(Job::class, 'saved_jobs')->withTimestamps();
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }

    // ---- HELPERS ----

    public function isAdmin(): bool  { return $this->role === 'admin'; }
    public function isCompany(): bool { return $this->role === 'company'; }
    public function isUser(): bool   { return $this->role === 'user'; }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) return \Storage::url($this->avatar);
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=2563eb&color=fff';
    }

    public function hasAppliedTo(Job $job): bool
    {
        return $this->jobApplications()->where('job_id', $job->id)->exists();
    }

    public function hasSaved(Job $job): bool
    {
        return $this->savedJobs()->where('job_id', $job->id)->exists();
    }

    public function getRecommendedJobs(int $limit = 10)
    {
        return app(\App\Services\JobRecommendationService::class)
               ->recommend($this, $limit);
    }
}