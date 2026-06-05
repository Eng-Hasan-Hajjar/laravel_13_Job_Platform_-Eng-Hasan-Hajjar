<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function index()
    {
        $company = auth()->user()->company;
        abort_if(!$company, 404, 'No company profile found.');

        return view('company.profile', compact('company'));
    }

    public function update(Request $request)
    {
        $company = auth()->user()->company;
        abort_if(!$company, 404);

        $request->validate([
            'name'            => 'required|string|max:255',
            'industry'        => 'nullable|string|max:200',
            'description'     => 'nullable|string|max:5000',
            'location'        => 'nullable|string|max:300',
            'website'         => 'nullable|url|max:500',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:30',
            'employees_count' => 'nullable|string|max:50',
            'founded_year'    => 'nullable|integer|min:1900|max:' . date('Y'),
            'facebook'        => 'nullable|url|max:500',
            'twitter'         => 'nullable|url|max:500',
            'linkedin'        => 'nullable|url|max:500',
            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // ── رفع شعار الشركة ──────────────────────────────────────
        if ($request->hasFile('logo')) {
            // حذف القديم
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $company->logo = $request->file('logo')->store('companies/logos', 'public');
        }

        // ── رفع صورة الغلاف ─────────────────────────────────────
        if ($request->hasFile('cover_image')) {
            if ($company->cover_image) {
                Storage::disk('public')->delete($company->cover_image);
            }
            $company->cover_image = $request->file('cover_image')->store('companies/covers', 'public');
        }

        // ── تحديث البيانات النصية ────────────────────────────────
        $company->fill($request->only([
            'name', 'industry', 'description', 'location',
            'website', 'email', 'phone',
            'employees_count', 'founded_year',
            'facebook', 'twitter', 'linkedin',
        ]));

        // تحديث الـ slug إذا تغيّر الاسم
        if ($company->isDirty('name')) {
            $company->slug = Str::slug($company->name) . '-' . Str::random(5);
        }

        $company->save();

        return back()->with('success', __('messages.profile_updated'));
    }

    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $company = auth()->user()->company;
        abort_if(!$company, 404);

        if ($company->logo) {
            Storage::disk('public')->delete($company->logo);
        }

        $company->update([
            'logo' => $request->file('logo')->store('companies/logos', 'public'),
        ]);

        return back()->with('success', __('messages.logo_updated'));
    }
}