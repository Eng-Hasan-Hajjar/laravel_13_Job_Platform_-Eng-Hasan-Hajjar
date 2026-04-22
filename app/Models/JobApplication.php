<?php

// ==========================================
// app/Models/JobApplication.php
// ==========================================
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class JobApplication extends Model
{
    use HasFactory;
 
    protected $fillable = [
        'user_id', 'job_id',
        'cover_letter', 'cv_path',
        'expected_salary', 'availability',
        'status', 'admin_notes',
        'reviewed_at', 'responded_at',
    ];
 
    protected $casts = [
        'reviewed_at'  => 'datetime',
        'responded_at' => 'datetime',
        'expected_salary' => 'decimal:2',
    ];
 
    public const STATUSES = ['pending', 'reviewed', 'shortlisted', 'accepted', 'rejected'];
 
    // ---- RELATIONSHIPS ----
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }
 
    public function job()
    {
        return $this->belongsTo(Job::class);
    }
 
    // ---- SCOPES ----
 
    public function scopeStatus($query, $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }
 
    // ---- HELPERS ----
 
    public function isPending(): bool     { return $this->status === 'pending'; }
    public function isAccepted(): bool    { return $this->status === 'accepted'; }
    public function isRejected(): bool    { return $this->status === 'rejected'; }
    public function isShortlisted(): bool { return $this->status === 'shortlisted'; }
 
    public function updateStatus(string $status, ?string $notes = null): bool
    {
        $this->status = $status;
        if ($notes) $this->admin_notes = $notes;
        if (in_array($status, ['accepted', 'rejected'])) {
            $this->responded_at = now();
        }
        return $this->save();
    }
}
