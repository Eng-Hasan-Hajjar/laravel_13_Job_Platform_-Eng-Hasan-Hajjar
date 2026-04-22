<?php


// ==========================================
// app/Models/Category.php
// ==========================================
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
 
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
 
    public function getJobsCountAttribute(): int
    {
        return $this->jobs()->active()->count();
    }
 
    public function getRouteKeyName(): string { return 'slug'; }


   // ✅ هذا هو الحل
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

}