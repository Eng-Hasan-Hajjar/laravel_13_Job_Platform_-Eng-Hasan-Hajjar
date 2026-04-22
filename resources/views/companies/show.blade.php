@extends('layouts.app')
@section('title', $company->name)

@section('content')

    <!-- Cover Image -->
    <div style="height:220px;background:linear-gradient(135deg,#1e3a5f,#2563eb,#7c3aed);position:relative;overflow:hidden">
        @if($company->cover_image)
        <img src="{{ Storage::url($company->cover_image) }}" alt="" style="width:100%;height:100%;object-fit:cover;opacity:.7">
        @else
        <div style="position:absolute;inset:0;background:url('data:image/svg+xml,...');opacity:.1"></div>
        @endif
        @if($company->is_verified)
        <div style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.2);backdrop-filter:blur(8px);color:white;padding:.375rem .875rem;border-radius:var(--radius-full);font-size:.8rem;font-weight:700">
            <i class="fas fa-check-circle"></i> {{ __('messages.verified') }}
        </div>
        @endif
    </div>

    <div class="page-container" style="margin-top:-4rem;position:relative;z-index:1">
        <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">

            <!-- LEFT -->
            <div>
                <!-- Company Header -->
                <div class="card" style="margin-bottom:1.5rem">
                    <div class="card-body">
                        <div style="display:flex;align-items:flex-end;gap:1.25rem;flex-wrap:wrap;margin-bottom:1.25rem">
                            <!-- Logo -->
                            <div style="width:80px;height:80px;border-radius:var(--radius-lg);border:4px solid var(--bg-card);overflow:hidden;box-shadow:var(--shadow-md);flex-shrink:0;background:var(--bg-hover)">
                                @if($company->logo)
                                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" style="width:100%;height:100%;object-fit:cover">
                                @else
                                    <div class="avatar" style="width:100%;height:100%;border-radius:0;font-size:1.5rem">
                                        {{ mb_strtoupper(mb_substr($company->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div style="flex:1">
                                <div style="display:flex;align-items:center;gap:.625rem;flex-wrap:wrap;margin-bottom:.25rem">
                                    <h1 style="font-size:1.5rem;font-weight:800">{{ $company->name }}</h1>
                                    @if($company->is_verified)
                                    <span style="color:var(--primary);font-size:1.125rem" data-tooltip="{{ __('messages.verified_company') }}">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    @endif
                                </div>
                                <div style="font-size:.9rem;color:var(--text-secondary)">{{ $company->industry }}</div>
                                <!-- Rating -->
                                @if($company->reviews_count > 0)
                                <div style="display:flex;align-items:center;gap:.375rem;margin-top:.375rem">
                                    @for($i=1;$i<=5;$i++)
                                    <i class="fas fa-star{{ $i <= round($company->average_rating) ? '' : '-o' }}" style="color:var(--warning);font-size:.875rem"></i>
                                    @endfor
                                    <span style="font-weight:700;font-size:.9rem">{{ number_format($company->average_rating, 1) }}</span>
                                    <span style="font-size:.8rem;color:var(--text-muted)">({{ $company->reviews_count }} {{ __('messages.reviews') }})</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Meta Info -->
                        <div style="display:flex;flex-wrap:wrap;gap:1.25rem;font-size:.85rem;color:var(--text-secondary);padding-top:1rem;border-top:1px solid var(--border)">
                            @if($company->location)
                            <span><i class="fas fa-map-marker-alt" style="color:var(--primary);width:16px"></i> {{ $company->location }}</span>
                            @endif
                            @if($company->employees_count)
                            <span><i class="fas fa-users" style="color:var(--primary);width:16px"></i> {{ $company->employees_count }} {{ __('messages.employees') }}</span>
                            @endif
                            @if($company->founded_year)
                            <span><i class="fas fa-calendar" style="color:var(--primary);width:16px"></i> {{ __('messages.founded') }} {{ $company->founded_year }}</span>
                            @endif
                            @if($company->website)
                            <a href="{{ $company->website }}" target="_blank" style="color:var(--primary);text-decoration:none">
                                <i class="fas fa-globe" style="width:16px"></i> {{ parse_url($company->website, PHP_URL_HOST) }}
                            </a>
                            @endif
                        </div>

                        <!-- Social Links -->
                        @if($company->linkedin || $company->twitter || $company->facebook)
                        <div style="display:flex;gap:.5rem;margin-top:.875rem">
                            @if($company->linkedin)
                            <a href="{{ $company->linkedin }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#0077b5">
                                <i class="fab fa-linkedin"></i>
                            </a>
                            @endif
                            @if($company->twitter)
                            <a href="{{ $company->twitter }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#1da1f2">
                                <i class="fab fa-twitter"></i>
                            </a>
                            @endif
                            @if($company->facebook)
                            <a href="{{ $company->facebook }}" target="_blank" class="btn btn-ghost btn-sm" style="color:#1877f2">
                                <i class="fab fa-facebook"></i>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Tabs -->
                <div style="display:flex;gap:.25rem;border-bottom:1px solid var(--border);margin-bottom:1.25rem">
                    @foreach([['id'=>'about','label'=>__('messages.about')],['id'=>'jobs','label'=>__('messages.open_positions').' ('.$company->active_jobs_count.')'],['id'=>'reviews','label'=>__('messages.reviews').' ('.$company->reviews_count.')']] as $t)
                    <button onclick="switchTab('{{ $t['id'] }}')" id="ctab-{{ $t['id'] }}"
                            style="padding:.75rem 1.25rem;border:none;background:none;cursor:pointer;font-size:.875rem;font-weight:600;color:var(--text-muted);border-bottom:2px solid transparent;transition:var(--transition);white-space:nowrap">
                        {{ $t['label'] }}
                    </button>
                    @endforeach
                </div>

                <!-- About Tab -->
                <div id="cpanel-about">
                    <div class="card">
                        <div class="card-body">
                            <div style="color:var(--text-secondary);line-height:1.8;font-size:.9rem">
                                {!! nl2br(e($company->description)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobs Tab -->
                <div id="cpanel-jobs" style="display:none">
                    <div style="display:flex;flex-direction:column;gap:.875rem">
                        @forelse($company->activeJobs()->with('category')->latest()->get() as $job)
                            @include('components.job-card', ['job' => $job])
                        @empty
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="fas fa-briefcase"></i></div>
                            <h3>{{ __('messages.no_open_positions') }}</h3>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div id="cpanel-reviews" style="display:none">
                    @auth
                    @if(auth()->user()->isUser())
                    <!-- Write Review Form -->
                    <div class="card" style="margin-bottom:1.25rem;border:2px dashed var(--border)">
                        <div class="card-body">
                            <h3 style="font-size:1rem;font-weight:700;margin-bottom:1rem">✍️ {{ __('messages.write_review') }}</h3>
                            <form action="{{ route('companies.review', $company) }}" method="POST" data-validate>
                                @csrf
                                <!-- Star Rating -->
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.rating') }} <span class="required">*</span></label>
                                    <div class="star-rating" id="starRating" style="display:flex;gap:.25rem;font-size:1.5rem">
                                        @for($i=1;$i<=5;$i++)
                                        <i class="far fa-star" data-value="{{ $i }}" onclick="setRating({{ $i }})"
                                           style="cursor:pointer;color:var(--warning);transition:var(--transition)"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="rating" id="ratingInput" value="{{ old('rating') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.review_title') }} <span class="required">*</span></label>
                                    <input type="text" name="title" class="form-control" required maxlength="150"
                                           value="{{ old('title') }}" placeholder="{{ __('messages.review_title_placeholder') }}">
                                </div>
                                <div class="grid grid-2">
                                    <div class="form-group">
                                        <label class="form-label">{{ __('messages.pros') }}</label>
                                        <textarea name="pros" class="form-control" rows="3"
                                                  placeholder="{{ __('messages.pros_placeholder') }}">{{ old('pros') }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">{{ __('messages.cons') }}</label>
                                        <textarea name="cons" class="form-control" rows="3"
                                                  placeholder="{{ __('messages.cons_placeholder') }}">{{ old('cons') }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.review_body') }} <span class="required">*</span></label>
                                    <textarea name="body" class="form-control" rows="4" required minlength="50"
                                              placeholder="{{ __('messages.review_body_placeholder') }}">{{ old('body') }}</textarea>
                                </div>
                                <label style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;cursor:pointer;font-size:.875rem">
                                    <input type="checkbox" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }} style="accent-color:var(--primary)">
                                    {{ __('messages.post_anonymously') }}
                                </label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> {{ __('messages.submit_review') }}
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                    @endauth

                    <!-- Reviews List -->
                    @forelse($company->reviews()->approved()->latest()->get() as $review)
                    <div class="card" style="margin-bottom:.875rem">
                        <div class="card-body">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.625rem">
                                <div style="display:flex;align-items:center;gap:.625rem">
                                    <div class="avatar avatar-sm">
                                        {{ mb_strtoupper(mb_substr($review->reviewer_name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:700;font-size:.875rem">{{ $review->reviewer_name }}</div>
                                        <div style="font-size:.75rem;color:var(--text-muted)">{{ $review->created_at->format('d M Y') }}</div>
                                    </div>
                                </div>
                                <div style="display:flex;gap:.125rem;color:var(--warning)">
                                    @for($i=1;$i<=5;$i++)
                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}" style="font-size:.875rem"></i>
                                    @endfor
                                </div>
                            </div>
                            <div style="font-weight:700;font-size:.9rem;margin-bottom:.5rem">{{ $review->title }}</div>
                            <p style="font-size:.875rem;color:var(--text-secondary);line-height:1.7">{{ $review->body }}</p>
                            @if($review->pros || $review->cons)
                            <div class="grid grid-2" style="margin-top:.875rem">
                                @if($review->pros)
                                <div style="padding:.625rem;background:rgba(16,185,129,.06);border-radius:var(--radius);border:1px solid rgba(16,185,129,.2)">
                                    <div style="font-size:.75rem;font-weight:700;color:var(--success);margin-bottom:.25rem">👍 {{ __('messages.pros') }}</div>
                                    <div style="font-size:.8rem;color:var(--text-secondary)">{{ $review->pros }}</div>
                                </div>
                                @endif
                                @if($review->cons)
                                <div style="padding:.625rem;background:rgba(239,68,68,.06);border-radius:var(--radius);border:1px solid rgba(239,68,68,.2)">
                                    <div style="font-size:.75rem;font-weight:700;color:var(--danger);margin-bottom:.25rem">👎 {{ __('messages.cons') }}</div>
                                    <div style="font-size:.8rem;color:var(--text-secondary)">{{ $review->cons }}</div>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <div class="empty-state-icon"><i class="fas fa-star"></i></div>
                        <h3>{{ __('messages.no_reviews_yet') }}</h3>
                        <p>{{ __('messages.be_first_reviewer') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- RIGHT Sidebar -->
            <div style="position:sticky;top:calc(var(--navbar-height) + 1rem)">
                <!-- Stats -->
                <div class="card" style="margin-bottom:1.25rem">
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.875rem">
                        @foreach([
                            ['icon'=>'fa-briefcase','label'=>__('messages.open_positions'),'value'=>$company->active_jobs_count,'color'=>'primary'],
                            ['icon'=>'fa-users','label'=>__('messages.total_applicants'),'value'=>\App\Models\JobApplication::whereHas('job',fn($q)=>$q->where('company_id',$company->id))->count(),'color'=>'success'],
                            ['icon'=>'fa-star','label'=>__('messages.rating'),'value'=>number_format($company->average_rating,1).'/5','color'=>'warning'],
                        ] as $s)
                        <div style="display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--bg-hover);border-radius:var(--radius)">
                            <div style="width:36px;height:36px;border-radius:var(--radius-sm);background:rgba(37,99,235,.1);display:flex;align-items:center;justify-content:center;color:var(--{{ $s['color'] }});font-size:.875rem;flex-shrink:0">
                                <i class="fas {{ $s['icon'] }}"></i>
                            </div>
                            <div style="flex:1">
                                <div style="font-size:.75rem;color:var(--text-muted)">{{ $s['label'] }}</div>
                            </div>
                            <span style="font-weight:800;font-size:1.125rem">{{ $s['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Contact -->
                <div class="card">
                    <div class="card-header"><span class="card-title">{{ __('messages.contact') }}</span></div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:.625rem;font-size:.85rem">
                        @if($company->email)
                        <a href="mailto:{{ $company->email }}" style="color:var(--text-secondary);text-decoration:none;display:flex;align-items:center;gap:.5rem">
                            <i class="fas fa-envelope" style="color:var(--primary);width:16px"></i> {{ $company->email }}
                        </a>
                        @endif
                        @if($company->phone)
                        <a href="tel:{{ $company->phone }}" style="color:var(--text-secondary);text-decoration:none;display:flex;align-items:center;gap:.5rem">
                            <i class="fas fa-phone" style="color:var(--primary);width:16px"></i> {{ $company->phone }}
                        </a>
                        @endif
                        @if($company->website)
                        <a href="{{ $company->website }}" target="_blank" style="color:var(--primary);text-decoration:none;display:flex;align-items:center;gap:.5rem">
                            <i class="fas fa-globe" style="width:16px"></i> {{ __('messages.visit_website') }}
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
function switchTab(id) {
    ['about','jobs','reviews'].forEach(t => {
        document.getElementById('cpanel-' + t).style.display = 'none';
        const btn = document.getElementById('ctab-' + t);
        btn.style.color = 'var(--text-muted)';
        btn.style.borderBottomColor = 'transparent';
    });
    document.getElementById('cpanel-' + id).style.display = 'block';
    const active = document.getElementById('ctab-' + id);
    active.style.color = 'var(--primary)';
    active.style.borderBottomColor = 'var(--primary)';
}
switchTab('about');

// Star rating
function setRating(val) {
    document.getElementById('ratingInput').value = val;
    document.querySelectorAll('.star-rating i').forEach((star, i) => {
        star.className = i < val ? 'fas fa-star' : 'far fa-star';
    });
}
document.querySelectorAll('.star-rating i').forEach(star => {
    star.addEventListener('mouseover', function() {
        const val = parseInt(this.dataset.value);
        document.querySelectorAll('.star-rating i').forEach((s, i) => {
            s.className = i < val ? 'fas fa-star' : 'far fa-star';
        });
    });
});
</script>
@endpush
@endsection