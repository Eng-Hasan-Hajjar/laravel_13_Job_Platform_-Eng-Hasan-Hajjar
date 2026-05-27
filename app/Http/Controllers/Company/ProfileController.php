<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Get the company associated with this user
        $company = $user->company;
        
        // If no company exists yet, you might want to create one or handle the error
        if (!$company) {
            // Option 1: Redirect to create company profile
            return redirect()->route('company.create')->with('error', 'Please complete your company profile first.');
            
            // Option 2: Create a new empty company (not recommended)
            // $company = new Company(['user_id' => $user->id]);
        }
        
        return view('company.profile', compact('company'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'employees_count' => 'nullable|string|max:50',
            'founded_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'linkedin' => 'nullable|url|max:255',
            'twitter' => 'nullable|url|max:255',
            'facebook' => 'nullable|url|max:255',
        ]);
        
        $company->update($validated);
        
        return back()->with('success', 'Profile updated successfully');
    }

    public function uploadLogo(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->back()->with('error', 'Company not found.');
        }
        
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($company->logo) {
                \Storage::delete($company->logo);
            }
            
            $path = $request->file('logo')->store('company/logos', 'public');
            $company->update(['logo' => $path]);
        }
        
        return back()->with('success', 'Logo uploaded successfully');
    }
}