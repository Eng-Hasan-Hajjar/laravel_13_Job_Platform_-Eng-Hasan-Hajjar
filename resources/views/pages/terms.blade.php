@extends('layouts.app')
@section('title', __('messages.terms'))
@section('content')
<div class="page-container" style="max-width:800px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">📋 {{ __('messages.terms') }}</h1>
    <div class="card"><div class="card-body" style="line-height:1.9;color:var(--text-secondary)">
        <h2 style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;color:var(--text-primary)">1. {{ app()->getLocale()==='ar' ? 'القبول بالشروط' : 'Acceptance of Terms' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'باستخدامك لمنصة JobScout فإنك توافق على الالتزام بهذه الشروط والأحكام. إذا كنت لا توافق على أي من هذه الشروط، يُرجى عدم استخدام المنصة.' : 'By using JobScout, you agree to be bound by these terms and conditions. If you do not agree to any of these terms, please do not use the platform.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">2. {{ app()->getLocale()==='ar' ? 'حسابات المستخدمين' : 'User Accounts' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'أنت مسؤول عن الحفاظ على سرية بيانات حسابك وكلمة المرور. يجب أن تكون المعلومات المقدمة دقيقة وحقيقية.' : 'You are responsible for maintaining the confidentiality of your account credentials. All information provided must be accurate and truthful.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">3. {{ app()->getLocale()==='ar' ? 'نشر الوظائف' : 'Job Posting' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'يجب أن تكون جميع إعلانات الوظائف المنشورة حقيقية وقانونية. تحتفظ المنصة بحق إزالة أي إعلان مخالف.' : 'All job postings must be genuine and lawful. The platform reserves the right to remove any violating listing.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">4. {{ app()->getLocale()==='ar' ? 'حقوق الملكية الفكرية' : 'Intellectual Property' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'جميع محتويات المنصة محمية بموجب قوانين حقوق الملكية الفكرية. لا يجوز نسخ أو إعادة توزيع أي محتوى بدون إذن مسبق.' : 'All platform content is protected by intellectual property laws. No content may be copied or redistributed without prior permission.' }}</p>
        <h2 style="font-size:1.1rem;font-weight:700;margin:1.25rem 0 .75rem;color:var(--text-primary)">5. {{ app()->getLocale()==='ar' ? 'إخلاء المسؤولية' : 'Disclaimer' }}</h2>
        <p>{{ app()->getLocale()==='ar' ? 'المنصة تعمل كوسيط بين أصحاب العمل والباحثين عن عمل. لا نتحمل مسؤولية أي اتفاقات تتم بين الطرفين خارج المنصة.' : 'The platform acts as an intermediary between employers and job seekers. We are not responsible for any agreements made between parties outside the platform.' }}</p>
        <p style="margin-top:1.5rem;font-size:.85rem;color:var(--text-muted)">{{ app()->getLocale()==='ar' ? 'آخر تحديث: يونيو 2026' : 'Last updated: June 2026' }}</p>
    </div></div>
</div>
@endsection