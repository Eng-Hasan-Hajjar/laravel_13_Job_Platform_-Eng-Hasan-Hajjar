<?php
// app/Models/Company.php
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class Company extends Model
{
    use HasFactory, SoftDeletes;
 
    protected $fillable = [
        'user_id', 'name', 'slug', 'logo', 'cover_image',
        'description', 'industry', 'website', 'location',
        'employees_count', 'founded_year', 'email', 'phone',
        'facebook', 'twitter', 'linkedin',
        'is_verified', 'is_active',
    ];
 
    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
    ];
 
    // ---- RELATIONSHIPS ----
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
 
    public function activeJobs()
    {
        return $this->jobs()->active();
    }
 
    public function reviews()
    {
        return $this->hasMany(CompanyReview::class);
    }
 
    // ---- COMPUTED ----
 
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }
 
    public function getActiveJobsCountAttribute(): int
    {
        return $this->activeJobs()->count();
    }
 
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }
 
    // ---- SCOPES ----
 
    public function scopeVerified($query) { return $query->where('is_verified', true); }
    public function scopeActive($query)   { return $query->where('is_active', true); }
 
    public function getRouteKeyName(): string { return 'slug'; }
}
 