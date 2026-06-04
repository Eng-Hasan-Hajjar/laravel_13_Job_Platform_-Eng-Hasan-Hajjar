<?php

namespace App\Http\Controllers;

use App\Models\{Company, CompanyReview};
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    //  قائمة الشركات (عام)
    //  الـ view يتوقع: $companies (paginated), $industries
    // ══════════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $query = Company::query()
            ->active()
            ->withCount(['jobs', 'reviews'])
            ->when($request->search, fn($q, $s) => $q->where(fn($sub) => $sub
                ->where('name', 'like', "%{$s}%")
                ->orWhere('industry', 'like', "%{$s}%")
                ->orWhere('description', 'like', "%{$s}%")
            ))
            ->when($request->industry, fn($q, $ind) => $q->where('industry', $ind));

        // الترتيب
        match ($request->sort ?? 'latest') {
            'rating' => $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating'),
            'jobs'   => $query->orderByDesc('jobs_count'),
            default  => $query->latest(),
        };

        $companies = $query->paginate(12)->withQueryString();

        // قائمة القطاعات للفلتر
        $industries = Company::active()
            ->whereNotNull('industry')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
            ->filter()
            ->values();

        return view('companies.index', compact('companies', 'industries'));
    }

    // ══════════════════════════════════════════════════════════════
    //  صفحة شركة واحدة (عام)
    // ══════════════════════════════════════════════════════════════
    public function show(Company $company)
    {
        abort_if(!$company->is_active, 404);

        $company->loadCount(['jobs', 'reviews']);

        // الوظائف النشطة للشركة
        $jobs = $company->jobs()->active()->with('category')->latest()->get();

        // هل قيّم المستخدم الحالي هذه الشركة من قبل؟
        $hasReviewed = false;
        if (auth()->check()) {
            $hasReviewed = $company->reviews()
                ->where('user_id', auth()->id())
                ->exists();
        }

        return view('companies.show', compact('company', 'jobs', 'hasReviewed'));
    }

    // ══════════════════════════════════════════════════════════════
    //  إضافة تقييم للشركة
    // ══════════════════════════════════════════════════════════════
    public function review(Request $request, Company $company)
    {
        $user = auth()->user();

        // المستخدمون فقط (ليس الشركات أو المشرفون)
        if (!$user->isUser()) {
            return back()->with('error', __('messages.only_users_can_review'));
        }

        // منع التقييم المكرر
        if ($company->reviews()->where('user_id', $user->id)->exists()) {
            return back()->with('error', __('messages.already_reviewed'));
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'required|string|max:200',
            'body'   => 'required|string|min:10|max:2000',
            'pros'   => 'nullable|string|max:1000',
            'cons'   => 'nullable|string|max:1000',
        ]);

        $company->reviews()->create([
            'user_id'       => $user->id,
            'reviewer_name' => $request->boolean('anonymous') ? __('messages.anonymous') : $user->name,
            'rating'        => $validated['rating'],
            'title'         => $validated['title'],
            'body'          => $validated['body'],
            'pros'          => $validated['pros'] ?? null,
            'cons'          => $validated['cons'] ?? null,
            'is_approved'   => false, // تتطلب موافقة المشرف
        ]);

        return back()->with('success', __('messages.review_submitted'));
    }
}