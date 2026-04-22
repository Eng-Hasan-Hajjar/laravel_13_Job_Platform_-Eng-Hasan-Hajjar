<?php



// ==========================================
// app/Http/Controllers/User/ProfileController.php
// ==========================================
namespace App\Http\Controllers\User;
 
use App\Http\Controllers\Controller;
use App\Services\CvAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Storage};
 
class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('jobApplications', 'savedJobs');
 
        $completeness = $this->calculateCompleteness($user);
 
        $stats = [
            'applications' => $user->jobApplications->count(),
            'accepted'     => $user->jobApplications->where('status', 'accepted')->count(),
            'saved'        => $user->savedJobs->count(),
            'views'        => 0,
        ];
 
        $recommendedJobs = $user->getRecommendedJobs(5);
 
        return view('user.profile', compact('user', 'completeness', 'stats', 'recommendedJobs'));
    }
 
    public function update(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:30',
            'location'         => 'nullable|string|max:200',
            'bio'              => 'nullable|string|max:2000',
            'experience_level' => 'nullable|in:entry,junior,mid,senior,lead',
            'skills'           => 'nullable|string|max:1000',
        ]);
 
        $skills = $request->skills
            ? array_map('trim', explode(',', $request->skills))
            : [];
 
        auth()->user()->update([
            'name'             => $request->name,
            'phone'            => $request->phone,
            'location'         => $request->location,
            'bio'              => $request->bio,
            'experience_level' => $request->experience_level,
            'skills'           => array_filter($skills),
        ]);
 
        return back()->with('success', __('messages.save_changes') . ' ✓');
    }
 
    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
 
        $user = auth()->user();
        if ($user->avatar) Storage::disk('public')->delete($user->avatar);
 
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);
 
        return back()->with('success', __('messages.save_changes') . ' ✓');
    }
 
    public function updatePrefs(Request $request)
    {
        $request->validate([
            'preferred_job_types' => 'nullable|array',
            'preferred_locations' => 'nullable|string',
            'expected_salary'     => 'nullable|numeric|min:0',
            'locale'              => 'nullable|in:ar,en',
        ]);
 
        $locations = $request->preferred_locations
            ? array_map('trim', explode(',', $request->preferred_locations))
            : [];
 
        auth()->user()->update([
            'preferred_job_types' => $request->preferred_job_types ?? [],
            'preferred_locations' => array_filter($locations),
            'expected_salary'     => $request->expected_salary,
            'locale'              => $request->locale,
        ]);
 
        if ($request->locale) {
            session(['locale' => $request->locale]);
        }
 
        return back()->with('success', __('messages.save_preferences') . ' ✓');
    }
 
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', function ($attr, $val, $fail) {
                if (!Hash::check($val, auth()->user()->password)) {
                    $fail('Current password is incorrect.');
                }
            }],
            'password' => 'required|min:8|confirmed',
        ]);
 
        auth()->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', __('messages.update_password') . ' ✓');
    }
 
    public function uploadCv(Request $request)
    {
        $request->validate(['cv_file' => 'required|file|mimes:pdf,doc,docx|max:5120']);
 
        $user = auth()->user();
        if ($user->cv_path) Storage::disk('private')->delete($user->cv_path);
 
        $path = $request->file('cv_file')->store('cvs/' . $user->id, 'private');
        $user->update(['cv_path' => $path, 'cv_analyzed' => null]);
 
        // Dispatch background analysis
        dispatch(new \App\Jobs\AnalyzeCvJob($user));
 
        return back()->with('success', __('messages.upload_cv') . ' ✓');
    }
 
    public function downloadCv()
    {
        $user = auth()->user();
        abort_if(!$user->cv_path, 404);
        return Storage::disk('private')->download($user->cv_path, 'CV-' . $user->name . '.pdf');
    }
 
    public function deleteCv()
    {
        $user = auth()->user();
        if ($user->cv_path) Storage::disk('private')->delete($user->cv_path);
        $user->update(['cv_path' => null, 'cv_analyzed' => null]);
        return back()->with('success', 'CV deleted');
    }
 
    public function savedJobs()
    {
        $jobs = auth()->user()->savedJobs()->with(['company', 'category'])->paginate(12);
        return view('user.saved-jobs', compact('jobs'));
    }
 
    private function calculateCompleteness($user): int
    {
        $fields = [
            'name', 'email', 'phone', 'location', 'bio',
            'cv_path', 'skills', 'experience_level', 'avatar',
        ];
        $filled = 0;
        foreach ($fields as $field) {
            $val = $user->$field;
            if ($val && (!is_array($val) || count($val) > 0)) $filled++;
        }
        return (int) round(($filled / count($fields)) * 100);
    }
}
 


