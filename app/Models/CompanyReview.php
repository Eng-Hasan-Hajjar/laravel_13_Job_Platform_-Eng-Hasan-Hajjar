<?php



// ==========================================
// app/Models/CompanyReview.php
// ==========================================
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class CompanyReview extends Model
{
    protected $fillable = [
        'company_id', 'user_id',
        'rating', 'title', 'body',
        'pros', 'cons',
        'is_anonymous', 'is_approved',
    ];
 
    protected $casts = [
        'is_anonymous' => 'boolean',
        'is_approved'  => 'boolean',
        'rating'       => 'integer',
    ];
 
    public function company() { return $this->belongsTo(Company::class); }
 
    public function user() { return $this->belongsTo(User::class); }
 
    public function scopeApproved($query) { return $query->where('is_approved', true); }
 
    public function getReviewerNameAttribute(): string
    {
        return $this->is_anonymous ? __('messages.anonymous') : $this->user->name;
    }
}

