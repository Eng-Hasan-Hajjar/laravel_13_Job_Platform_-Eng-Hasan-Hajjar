<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->route('company.profile')
                ->with('warning', 'Please complete your company profile first.');
        }
        
        // Load reviews with user relationship
        $reviews = $company->reviews()
            ->with('user')
            ->latest()
            ->paginate(10);
        
        // Calculate statistics
        $stats = [
            'average_rating' => $company->average_rating,
            'total_reviews' => $company->reviews_count,
            'rating_distribution' => $this->getRatingDistribution($company),
            'rating_trend' => $this->getRatingTrend($company),
        ];
        
        return view('company.reviews', compact('company', 'reviews', 'stats'));
    }
    
    public function show($id)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->route('company.profile')
                ->with('warning', 'Please complete your company profile first.');
        }
        
        $review = $company->reviews()
            ->with('user')
            ->findOrFail($id);
        
        return view('company.review-detail', compact('company', 'review'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }
        
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
        ]);
        
        $review = $company->reviews()->findOrFail($id);
        
        // Use is_approved field instead of status
        $review->is_approved = $request->status === 'approved';
        $review->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Review status updated successfully',
                'status' => $request->status
            ]);
        }
        
        return back()->with('success', 'Review status updated successfully');
    }
    
    public function destroy($id)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->route('company.profile')
                ->with('warning', 'Please complete your company profile first.');
        }
        
        $review = $company->reviews()->findOrFail($id);
        $review->delete();
        
        return back()->with('success', 'Review deleted successfully');
    }
    
    public function reply(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Company not found'], 404);
        }
        
        $request->validate([
            'reply' => 'required|string|max:1000',
        ]);
        
        $review = $company->reviews()->findOrFail($id);
        
        // Add company_reply field - you may need to add this column to your table
        // Run migration: php artisan make:migration add_company_reply_to_company_reviews_table
        $review->company_reply = $request->reply;
        $review->replied_at = now();
        $review->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Reply added successfully',
                'reply' => $request->reply,
                'replied_at' => now()->format('M d, Y')
            ]);
        }
        
        return back()->with('success', 'Reply added successfully');
    }
    
    private function getRatingDistribution($company)
    {
        $distribution = [
            5 => 0,
            4 => 0,
            3 => 0,
            2 => 0,
            1 => 0,
        ];
        
        $ratings = $company->reviews()
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->get();
        
        foreach ($ratings as $rating) {
            if (isset($distribution[$rating->rating])) {
                $distribution[$rating->rating] = $rating->count;
            }
        }
        
        return $distribution;
    }
    
    private function getRatingTrend($company)
    {
        $trend = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $avgRating = $company->reviews()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->avg('rating');
            
            $trend[] = [
                'month' => $month->format('M Y'),
                'rating' => round($avgRating ?? 0, 1),
                'count' => $company->reviews()
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }
        
        return $trend;
    }
    
    public function export(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found');
        }
        
        $reviews = $company->reviews()
            ->with('user')
            ->latest()
            ->get();
        
        $filename = "reviews_{$company->id}_{date('Y-m-d')}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function () use ($reviews) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Arabic support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID', 'Reviewer Name', 'Reviewer Email', 'Rating', 
                'Title', 'Comment', 'Pros', 'Cons', 'Is Anonymous', 
                'Is Approved', 'Company Reply', 'Date'
            ]);
            
            // Data rows
            foreach ($reviews as $review) {
                fputcsv($file, [
                    $review->id,
                    $review->user->name,
                    $review->user->email,
                    $review->rating,
                    $review->title ?? '',
                    $review->body ?? '',
                    $review->pros ?? '',
                    $review->cons ?? '',
                    $review->is_anonymous ? 'Yes' : 'No',
                    $review->is_approved ? 'Approved' : 'Pending',
                    $review->company_reply ?? '',
                    $review->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}