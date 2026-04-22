<?php


// ==========================================
// app/Jobs/NotifySubscribedUsers.php
// ==========================================
namespace App\Jobs;
 
use App\Models\{Job, User};
use App\Notifications\NewJobPosted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
 
class NotifySubscribedUsers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    public int $tries = 2;
 
    public function __construct(public Job $job) {}
 
    public function handle(): void
    {
        // Find users whose preferences match this job
        User::where('role', 'user')
            ->where('is_active', true)
            ->get()
            ->filter(fn($user) => $this->matchesUser($user))
            ->each(fn($user) => $user->notify(new NewJobPosted($this->job)));
    }
 
    private function matchesUser(User $user): bool
    {
        // Skills match
        $userSkills = array_map('mb_strtolower', $user->skills ?? []);
        $jobSkills  = array_map('mb_strtolower', $this->job->skills ?? []);
        if (!empty($userSkills) && !empty($jobSkills)) {
            if (count(array_intersect($userSkills, $jobSkills)) > 0) return true;
        }
 
        // Job type preference
        if (!empty($user->preferred_job_types) && in_array($this->job->type, $user->preferred_job_types)) {
            return true;
        }
 
        // Location preference
        if (!empty($user->preferred_locations)) {
            foreach ($user->preferred_locations as $loc) {
                if (str_contains(mb_strtolower($this->job->location), mb_strtolower($loc))) {
                    return true;
                }
            }
        }
 
        return false;
    }
}
 

