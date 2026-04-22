@extends('layouts.app')
@section('title', 'لوحة المتصدرين')
@section('content')
<div class="page-header mb-4">
    <h1><i class="bi bi-trophy me-2"></i>لوحة المتصدرين</h1>
    <p>أكثر المتطوعين إسهاماً في إعادة الإعمار</p>
</div>
<div class="card">
    <div class="card-body p-0">
        @foreach($volunteers as $i => $vol)
        @php $pos = $i + 1; $profile = $vol->volunteerProfile; @endphp
        <div class="d-flex align-items-center gap-3 p-3 border-bottom {{ $pos <= 3 ? 'bg-'.['warning','secondary',''][min($pos-1,2)].'-subtle' : '' }}" style="{{ $pos === 1 ? 'background:linear-gradient(90deg,#fff8e1,#fff) !important;' : '' }}">
            <div style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.1rem;flex-shrink:0;
                background:{{ $pos === 1 ? '#fbbf24' : ($pos === 2 ? '#94a3b8' : ($pos === 3 ? '#a16207' : 'var(--border)')) }};
                color:{{ $pos <= 3 ? '#fff' : 'var(--text-mid)' }};">
                {{ $pos <= 3 ? ['🥇','🥈','🥉'][$pos-1] : $pos }}
            </div>
            <img src="{{ $vol->avatar_url }}" style="width:44px;height:44px;border-radius:50%;border:2px solid var(--border);">
            <div class="flex-grow-1">
                <a href="{{ route('volunteers.show', $vol) }}" style="font-weight:700;text-decoration:none;color:var(--text-dark);">{{ $vol->name }}</a>
                @if($vol->city)<div style="font-size:.82rem;color:var(--text-light);"><i class="bi bi-geo-alt me-1"></i>{{ $vol->city }}</div>@endif
            </div>
            @if($profile)
            <div class="d-flex gap-4 text-center">
                <div><div style="font-weight:800;font-size:1.1rem;color:var(--primary);">{{ $profile->points }}</div><small class="text-muted" style="font-size:.72rem;">نقطة</small></div>
                <div><div style="font-weight:800;font-size:1.1rem;">{{ $profile->total_hours_contributed }}</div><small class="text-muted" style="font-size:.72rem;">ساعة</small></div>
                <div><div style="font-weight:800;font-size:1.1rem;">{{ $profile->completed_projects }}</div><small class="text-muted" style="font-size:.72rem;">مشروع</small></div>
            </div>
            @php $badge = $profile->badge; @endphp
            <span style="font-size:1.4rem;" title="{{ $badge['label'] }}">{{ $badge['icon'] }}</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endsection