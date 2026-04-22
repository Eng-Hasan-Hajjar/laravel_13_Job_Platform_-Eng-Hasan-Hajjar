<?php



// ==========================================
// app/Http/Controllers/Company/JobController.php
// ==========================================
namespace App\Http\Controllers\Company;
 
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Category;
use App\Notifications\NewJobPosted;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
 
class JobController extends Controller
{
    public function __construct()
    {
        $this->middleware(fn($req, $next) => auth()->user()->isCompany()
            ? $next($req) : abort(403));
    }
 
    private function company() { return auth()->user()->company; }
 
    public function index(Request $request)
    {
        $jobs = $this->company()->jobs()
            ->withCount('applications')
            ->when($request->status === 'active', fn($q) => $q->active())
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(15);
 
        return view('company.jobs.index', compact('jobs'));
    }
 
    public function create()
    {
        $categories = Category::active()->get();
        return view('company.jobs.create', compact('categories'));
    }
 
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|min:5|max:200',
            'category_id'      => 'required|exists:categories,id',
            'description'      => 'required|min:100',
            'requirements'     => 'nullable|string',
            'benefits'         => 'nullable|string',
            'type'             => 'required|in:full-time,part-time,freelance,remote,internship',
            'location'         => 'required|max:200',
            'is_remote'        => 'boolean',
            'salary_min'       => 'nullable|numeric|min:0',
            'salary_max'       => 'nullable|numeric|min:0|gte:salary_min',
            'salary_currency'  => 'nullable|string|max:5',
            'experience_level' => 'required|in:entry,junior,mid,senior,lead',
            'skills'           => 'nullable|string',
            'deadline'         => 'nullable|date|after:today',
        ]);
 
        $validated['company_id'] = $this->company()->id;
        $validated['slug']       = Str::slug($validated['title']) . '-' . Str::random(6);
        $validated['skills']     = $request->skills
            ? array_map('trim', explode(',', $request->skills))
            : [];
 
        $job = Job::create($validated);
 
        // Notify subscribed users about new job
        dispatch(new \App\Jobs\NotifySubscribedUsers($job));
 
        return redirect()->route('company.jobs.show', $job)
                         ->with('success', __('messages.job_created'));
    }
 
    public function edit(Job $job)
    {
        abort_if($job->company_id !== $this->company()->id, 403);
        $categories = Category::active()->get();
        return view('company.jobs.edit', compact('job', 'categories'));
    }
 
    public function update(Request $request, Job $job)
    {
        abort_if($job->company_id !== $this->company()->id, 403);
 
        $validated = $request->validate([
            'title'            => 'required|min:5|max:200',
            'category_id'      => 'required|exists:categories,id',
            'description'      => 'required|min:100',
            'requirements'     => 'nullable|string',
            'benefits'         => 'nullable|string',
            'type'             => 'required|in:full-time,part-time,freelance,remote,internship',
            'location'         => 'required|max:200',
            'is_remote'        => 'boolean',
            'salary_min'       => 'nullable|numeric|min:0',
            'salary_max'       => 'nullable|numeric|gte:salary_min',
            'experience_level' => 'required|in:entry,junior,mid,senior,lead',
            'skills'           => 'nullable|string',
            'deadline'         => 'nullable|date',
            'is_active'        => 'boolean',
        ]);
 
        $validated['skills'] = $request->skills
            ? array_map('trim', explode(',', $request->skills))
            : [];
 
        $job->update($validated);
 
        return redirect()->route('company.jobs.index')
                         ->with('success', __('messages.job_updated'));
    }
 
    public function destroy(Job $job)
    {
        abort_if($job->company_id !== $this->company()->id, 403);
        $job->delete();
        return redirect()->route('company.jobs.index')
                         ->with('success', __('messages.job_deleted'));
    }
 
    public function toggleActive(Job $job)
    {
        abort_if($job->company_id !== $this->company()->id, 403);
        $job->update(['is_active' => !$job->is_active]);
        return response()->json(['is_active' => $job->is_active]);
    }
}
 


