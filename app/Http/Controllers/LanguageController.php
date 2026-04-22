<?php



// ==========================================
// app/Http/Controllers/LanguageController.php
// ==========================================
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
 
class LanguageController extends Controller
{
    public function switch(string $locale)
    {
        abort_if(!in_array($locale, ['ar', 'en']), 400);
 
        session(['locale' => $locale]);
 
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
 
        return redirect()->back()->withCookie(cookie()->forever('locale', $locale));
    }
}

