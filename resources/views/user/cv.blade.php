@extends('layouts.app')
@section('title', __('messages.my_cv'))
@section('content')
<div class="page-container" style="max-width:800px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">
        <i class="fas fa-file-pdf" style="color:var(--primary)"></i> {{ __('messages.my_cv') }}
    </h1>

    <div class="card">
        <div class="card-body" style="text-align:center;padding:2rem">
            @if(auth()->user()->cv_path)
                <i class="fas fa-file-pdf" style="font-size:3rem;color:#ef4444;margin-bottom:1rem;display:block"></i>
                <p style="font-weight:600;margin-bottom:1rem">{{ __('messages.cv_uploaded') }}</p>
                <div style="display:flex;gap:.75rem;justify-content:center;flex-wrap:wrap">
                    <a href="{{ route('user.cv.download') }}" class="btn btn-primary">
                        <i class="fas fa-download"></i> {{ __('messages.download_cv') }}
                    </a>
                    <form action="{{ route('user.cv.delete') }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete_cv') }}')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline" style="color:#ef4444;border-color:#ef4444">
                            <i class="fas fa-trash"></i> {{ __('messages.delete_cv') }}
                        </button>
                    </form>
                </div>
            @else
                <i class="fas fa-cloud-upload-alt" style="font-size:3rem;color:var(--text-muted);margin-bottom:1rem;display:block"></i>
                <p style="font-weight:600;margin-bottom:.5rem">{{ __('messages.no_cv_yet') }}</p>
                <p style="color:var(--text-secondary);font-size:.875rem;margin-bottom:1.5rem">{{ __('messages.upload_cv_desc') }}</p>
            @endif

            <form action="{{ route('user.cv.upload') }}" method="POST" enctype="multipart/form-data" style="margin-top:1rem">
                @csrf
                <div style="border:2px dashed var(--border);border-radius:var(--radius-lg);padding:1.5rem;margin-bottom:1rem">
                    <input type="file" name="cv" accept=".pdf,.doc,.docx" required
                           style="width:100%;cursor:pointer">
                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:.5rem">PDF, DOC, DOCX — {{ __('messages.max_size') }}: 5MB</p>
                </div>
                @error('cv')<div class="form-error">{{ $message }}</div>@enderror
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload"></i> {{ __('messages.upload_cv') }}
                </button>
            </form>
        </div>
    </div>

    @if(auth()->user()->cv_analyzed)
    <div class="card" style="margin-top:1.25rem">
        <div class="card-header"><span class="card-title">{{ __('messages.cv_analysis') }}</span></div>
        <div class="card-body">
            @php $analysis = auth()->user()->cv_analyzed; @endphp
            @if(!empty($analysis['skills']))
            <div style="margin-bottom:1rem">
                <div style="font-weight:600;margin-bottom:.5rem">{{ __('messages.extracted_skills') }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:.25rem">
                    @foreach($analysis['skills'] as $skill)
                    <span style="padding:.25rem .75rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.8rem;font-weight:600">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection