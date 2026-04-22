<?php


// ==========================================
// app/Jobs/AnalyzeCvJob.php
// ==========================================
namespace App\Jobs;
 
use App\Models\User;
use App\Services\CvAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
 
class AnalyzeCvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
 
    public int $tries = 3;
    public int $timeout = 120;
 
    public function __construct(public User $user) {}
 
    public function handle(CvAnalysisService $service): void
    {
        try {
            $service->analyzeUser($this->user);
            Log::info("CV analyzed for user {$this->user->id}");
        } catch (\Throwable $e) {
            Log::error("CV analysis failed for user {$this->user->id}: " . $e->getMessage());
        }
    }
}
 