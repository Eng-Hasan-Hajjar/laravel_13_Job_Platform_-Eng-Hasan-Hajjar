@extends('layouts.app')
@section('title', __('messages.saved_jobs'))
@section('content')
<div class="page-container">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">
        <i class="fas fa-bookmark" style="color:var(--warning)"></i> {{ __('messages.saved_jobs') }}
    </h1>

    @forelse($savedJobs as $job)
        @include('components.job-card', ['job' => $job])
    @empty
        <div class="card"><div class="card-body" style="text-align:center;padding:3rem">
            <i class="fas fa-bookmark" style="font-size:3rem;color:var(--text-muted);margin-bottom:1rem;display:block"></i>
            <h3 style="font-weight:700;margin-bottom:.5rem">{{ __('messages.no_saved_jobs') }}</h3>
            <p style="color:var(--text-secondary);margin-bottom:1rem">{{ __('messages.no_saved_jobs_desc') }}</p>
            <a href="{{ route('jobs.index') }}" class="btn btn-primary"><i class="fas fa-search"></i> {{ __('messages.browse_jobs') }}</a>
        </div></div>
    @endforelse

    @if($savedJobs->hasPages())
        <div style="margin-top:1.5rem">{{ $savedJobs->links() }}</div>
    @endif
</div>
@endsection