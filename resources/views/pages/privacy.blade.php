@extends('layouts.app')
@section('title', __('messages.privacy'))
@section('content')
<div class="page-container" style="max-width:800px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">🔒 {{ __('messages.privacy') }}</h1>
    <div class="card"><div class="card-body" style="line-height:1.9;color:var(--text-secondary)">
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;color:var(--text-primary)">{{ app()->getLocale()==='ar' ? 'البيانات التي نجمعها' : 'Data We Collect' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'نجمع المعلومات التي تقدمها عند التسجيل مثل الاسم والبريد الإلكتروني والسيرة الذاتية والمهارات. كما نجمع بيانات الاستخدام لتحسين تجربتك.' : 'We collect information you provide during registration such as name, email, CV, and skills. We also collect usage data to improve your experience.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">{{ app()->getLocale()==='ar' ? 'كيف نستخدم بياناتك' : 'How We Use Your Data' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'نستخدم بياناتك لتوفير خدمات التوظيف، وتقديم توصيات وظائف مخصصة، وإرسال إشعارات حول الفرص المناسبة، وتحسين أداء المنصة.' : 'We use your data to provide recruitment services, offer personalized job recommendations, send notifications about suitable opportunities, and improve platform performance.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">{{ app()->getLocale()==='ar' ? 'حماية البيانات' : 'Data Protection' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'نستخدم تشفير SSL وتقنيات أمان متقدمة لحماية بياناتك. السير الذاتية تُخزّن في خوادم آمنة غير قابلة للوصول العام.' : 'We use SSL encryption and advanced security technologies to protect your data. CVs are stored on secure private servers.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">{{ app()->getLocale()==='ar' ? 'حقوقك' : 'Your Rights' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'يحق لك طلب تصدير بياناتك أو حذف حسابك في أي وقت من صفحة الإعدادات.' : 'You have the right to request data export or delete your account at any time from the Settings page.' }}</p>
        <p style="margin-top:1.5rem;font-size:.85rem;color:var(--text-muted)">{{ app()->getLocale()==='ar' ? 'آخر تحديث: يونيو 2026' : 'Last updated: June 2026' }}</p>
    </div></div>
</div>
@endsection