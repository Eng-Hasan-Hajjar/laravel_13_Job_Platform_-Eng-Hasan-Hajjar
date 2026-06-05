@extends('layouts.app')
@section('title', __('messages.contact'))
@section('content')
<div class="page-container" style="max-width:800px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">📬 {{ app()->getLocale()==='ar' ? 'تواصل معنا' : 'Contact Us' }}</h1>
    <div class="card"><div class="card-body">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem">
            <div style="padding:1.25rem;background:var(--bg-hover);border-radius:var(--radius);text-align:center">
                <i class="fas fa-envelope" style="font-size:1.5rem;color:var(--primary);margin-bottom:.5rem;display:block"></i>
                <div style="font-weight:700;margin-bottom:.25rem">{{ app()->getLocale()==='ar' ? 'البريد الإلكتروني' : 'Email' }}</div>
                <a href="mailto:support@jobscout.com" style="color:var(--primary);text-decoration:none;font-size:.875rem">support@jobscout.com</a>
            </div>
            <div style="padding:1.25rem;background:var(--bg-hover);border-radius:var(--radius);text-align:center">
                <i class="fas fa-map-marker-alt" style="font-size:1.5rem;color:var(--primary);margin-bottom:.5rem;display:block"></i>
                <div style="font-weight:700;margin-bottom:.25rem">{{ app()->getLocale()==='ar' ? 'الموقع' : 'Location' }}</div>
                <span style="font-size:.875rem;color:var(--text-secondary)">{{ app()->getLocale()==='ar' ? 'جامعة حلب في المناطق المحررة' : 'University of Aleppo' }}</span>
            </div>
        </div>
        <form style="display:flex;flex-direction:column;gap:1rem">
            <div class="grid grid-2">
                <div class="form-group"><label class="form-label">{{ app()->getLocale()==='ar' ? 'الاسم' : 'Name' }}</label><input type="text" class="form-control" required></div>
                <div class="form-group"><label class="form-label">{{ app()->getLocale()==='ar' ? 'البريد' : 'Email' }}</label><input type="email" class="form-control" required></div>
            </div>
            <div class="form-group"><label class="form-label">{{ app()->getLocale()==='ar' ? 'الموضوع' : 'Subject' }}</label><input type="text" class="form-control"></div>
            <div class="form-group"><label class="form-label">{{ app()->getLocale()==='ar' ? 'الرسالة' : 'Message' }}</label><textarea class="form-control" rows="5" required></textarea></div>
            <button type="submit" class="btn btn-primary" style="align-self:flex-start;padding:.75rem 2rem"><i class="fas fa-paper-plane"></i> {{ app()->getLocale()==='ar' ? 'إرسال' : 'Send' }}</button>
        </form>
    </div></div>
</div>
@endsection