@extends('layouts.app')
@section('title', __('messages.about'))
@section('content')
<div class="page-container" style="max-width:800px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">💼 {{ app()->getLocale()==='ar' ? 'عن المنصة' : 'About Us' }}</h1>
    <div class="card" style="margin-bottom:1.25rem"><div class="card-body" style="line-height:1.9;color:var(--text-secondary)">
        <p style="font-size:1rem">{{ app()->getLocale()==='ar' ? 'JobScout هي منصة توظيف ذكية تهدف لربط الباحثين عن عمل بأفضل الشركات في المنطقة العربية. تم بناؤها كمشروع تخرج في جامعة حلب    — كلية الهندسة المعلوماتية.' : 'JobScout is a smart recruitment platform that connects job seekers with top companies in the Arab region. Built as a graduation project at the University of Aleppo — Faculty of Information Technology Engineering.' }}</p>
    </div></div>
    <div class="card" style="margin-bottom:1.25rem"><div class="card-header"><span class="card-title">{{ app()->getLocale()==='ar' ? 'مميزاتنا' : 'Our Features' }}</span></div><div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem">
            @foreach([
                ['🤖', app()->getLocale()==='ar' ? 'توصيات ذكية بالذكاء الاصطناعي' : 'AI-Powered Recommendations'],
                ['📄', app()->getLocale()==='ar' ? 'تحليل السيرة الذاتية تلقائياً' : 'Automatic CV Analysis'],
                ['🌐', app()->getLocale()==='ar' ? 'دعم العربية والإنجليزية' : 'Arabic & English Support'],
                ['🌙', app()->getLocale()==='ar' ? 'وضع داكن وفاتح' : 'Dark & Light Mode'],
                ['🔔', app()->getLocale()==='ar' ? 'إشعارات فورية' : 'Real-time Notifications'],
                ['⭐', app()->getLocale()==='ar' ? 'تقييم الشركات' : 'Company Reviews'],
            ] as [$icon, $text])
            <div style="padding:1rem;background:var(--bg-hover);border-radius:var(--radius);text-align:center">
                <div style="font-size:1.5rem;margin-bottom:.5rem">{{ $icon }}</div>
                <div style="font-size:.85rem;font-weight:600">{{ $text }}</div>
            </div>
            @endforeach
        </div>
    </div></div>
    <div class="card"><div class="card-header"><span class="card-title">{{ app()->getLocale()==='ar' ? 'فريق العمل' : 'Team' }}</span></div><div class="card-body">
        <p style="font-weight:600;margin-bottom:.5rem">{{ app()->getLocale()==='ar' ? 'إعداد الطالبات:' : 'Prepared by:' }}</p>
        <p>آفين عادل مركو، خدوج محمد كوجر، روليان علي أحمد، كاترين جمال يوسف، موليده خوشناف ديكو</p>
        <p style="font-weight:600;margin-top:1rem;margin-bottom:.5rem">{{ app()->getLocale()==='ar' ? 'بإشراف:' : 'Supervised by:' }}</p>
        <p>{{ app()->getLocale()==='ar' ? 'د. أحمد حاج درويش — د. بدر الدين قصاب' : 'Dr. Ahmad Haj Darwish — Dr. Badr Al-Din Qassab' }}</p>
    </div></div>
</div>
@endsection