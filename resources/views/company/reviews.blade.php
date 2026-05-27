@extends('layouts.app')
@section('title', __('messages.company_reviews'))

@section('content')
<div class="page-container">
    
    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">
                {{ __('messages.company_reviews') }}
            </h1>
            <p style="color:var(--text-secondary);font-size:.875rem">
                {{ __('messages.reviews_for') }} <strong>{{ $company->name }}</strong>
            </p>
        </div>
        <a href="{{ route('company.reviews.export') }}" class="btn btn-ghost">
            <i class="fas fa-download"></i> {{ __('messages.export_reviews') }}
        </a>
    </div>
    
    <!-- Stats Overview -->
    <div class="grid grid-3" style="margin-bottom:1.5rem">
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-star"></i></div>
            <div>
                <div class="stat-value">{{ number_format($stats['average_rating'], 1) }}</div>
                <div class="stat-label">{{ __('messages.average_rating') }}</div>
                <div class="rating-stars">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="color:{{ $i <= round($stats['average_rating']) ? '#fbbf24' : '#e2e8f0' }};font-size:.75rem"></i>
                    @endfor
                </div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-comments"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total_reviews'] }}</div>
                <div class="stat-label">{{ __('messages.total_reviews') }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="stat-value">{{ $stats['rating_trend'][0]['rating'] ?? 0 }}</div>
                <div class="stat-label">{{ __('messages.current_month_rating') }}</div>
            </div>
        </div>
    </div>
    
    <!-- Rating Distribution -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header">
            <span class="card-title">{{ __('messages.rating_distribution') }}</span>
        </div>
        <div class="card-body">
            @foreach([5,4,3,2,1] as $rating)
            <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem">
                <div style="width:60px;font-size:.875rem">{{ $rating }} ★</div>
                <div style="flex:1;background:var(--background-light);border-radius:var(--radius);overflow:hidden">
                    <div style="width:{{ $stats['total_reviews'] > 0 ? ($stats['rating_distribution'][$rating] / $stats['total_reviews'] * 100) : 0 }}%;
                                background:var(--primary);height:24px;border-radius:var(--radius);
                                transition:width 0.3s ease"></div>
                </div>
                <div style="width:80px;font-size:.75rem;color:var(--text-muted)">
                    {{ $stats['rating_distribution'][$rating] }} ({{ $stats['total_reviews'] > 0 ? round($stats['rating_distribution'][$rating] / $stats['total_reviews'] * 100) : 0 }}%)
                </div>
            </div>
            @endforeach
        </div>
    </div>
    
    <!-- Reviews List -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">{{ __('messages.all_reviews') }}</span>
        </div>
        
        @forelse($reviews as $review)
        <div style="padding:1.25rem;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:0.75rem">
                <div style="display:flex;align-items:center;gap:0.75rem">
                    <div class="avatar">{{ mb_strtoupper(mb_substr($review->user->name,0,2)) }}</div>
                    <div>
                        <div style="font-weight:600">{{ $review->user->name }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted)">
                            {{ $review->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
                <div>
                    <div class="rating-stars" style="display:inline-flex;gap:2px">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star" style="color:{{ $i <= $review->rating ? '#fbbf24' : '#e2e8f0' }};font-size:.875rem"></i>
                        @endfor
                    </div>
                    <span class="status-badge {{ $review->status }}" style="margin-left:0.75rem">
                        {{ __('messages.' . $review->status) }}
                    </span>
                </div>
            </div>
            
            @if($review->title)
            <h4 style="font-weight:600;margin-bottom:0.5rem">{{ $review->title }}</h4>
            @endif
            
            <p style="color:var(--text-secondary);margin-bottom:1rem;line-height:1.5">{{ $review->comment }}</p>
            
            @if($review->company_reply)
            <div style="background:var(--background-light);border-radius:var(--radius);padding:1rem;margin-top:0.75rem;border-left:3px solid var(--primary)">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem">
                    <i class="fas fa-reply" style="color:var(--primary);font-size:.75rem"></i>
                    <span style="font-weight:600;font-size:.875rem">{{ __('messages.company_reply') }}</span>
                    <span style="font-size:.7rem;color:var(--text-muted)">{{ \Carbon\Carbon::parse($review->replied_at)->format('M d, Y') }}</span>
                </div>
                <p style="font-size:.875rem">{{ $review->company_reply }}</p>
            </div>
            @else
            <div style="margin-top:0.75rem">
                <button onclick="showReplyForm({{ $review->id }})" class="btn btn-ghost btn-sm">
                    <i class="fas fa-reply"></i> {{ __('messages.reply_to_review') }}
                </button>
            </div>
            
            <!-- Reply Form (hidden by default) -->
            <div id="reply-form-{{ $review->id }}" style="display:none;margin-top:1rem">
                <form onsubmit="submitReply(event, {{ $review->id }})">
                    @csrf
                    <textarea name="reply" rows="3" class="form-control" placeholder="{{ __('messages.write_reply_here') }}" required></textarea>
                    <div style="display:flex;gap:0.5rem;margin-top:0.5rem">
                        <button type="submit" class="btn btn-primary btn-sm">{{ __('messages.submit_reply') }}</button>
                        <button type="button" onclick="hideReplyForm({{ $review->id }})" class="btn btn-ghost btn-sm">{{ __('messages.cancel') }}</button>
                    </div>
                </form>
            </div>
            @endif
            
            <div style="display:flex;gap:0.5rem;margin-top:1rem">
                <select onchange="updateReviewStatus({{ $review->id }}, this.value)" class="form-control" style="width:auto;font-size:.75rem;padding:.25rem .5rem">
                    <option value="approved" {{ $review->status === 'approved' ? 'selected' : '' }}>{{ __('messages.approved') }}</option>
                    <option value="pending" {{ $review->status === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="rejected" {{ $review->status === 'rejected' ? 'selected' : '' }}>{{ __('messages.rejected') }}</option>
                </select>
                <button onclick="deleteReview({{ $review->id }})" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                </button>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:3rem;text-align:center">
            <i class="fas fa-star" style="font-size:3rem;color:var(--text-muted);margin-bottom:1rem;opacity:.5"></i>
            <p>{{ __('messages.no_reviews_yet') }}</p>
            <p style="font-size:.875rem;color:var(--text-muted);margin-top:.5rem">{{ __('messages.reviews_will_appear_here') }}</p>
        </div>
        @endforelse
        
        @if($reviews->hasPages())
        <div style="padding:1rem;border-top:1px solid var(--border)">
            {{ $reviews->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function showReplyForm(reviewId) {
    document.getElementById(`reply-form-${reviewId}`).style.display = 'block';
}

function hideReplyForm(reviewId) {
    document.getElementById(`reply-form-${reviewId}`).style.display = 'none';
}

async function submitReply(event, reviewId) {
    event.preventDefault();
    const form = event.target;
    const reply = form.querySelector('textarea[name="reply"]').value;
    
    try {
        const response = await fetch(`/company/reviews/${reviewId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ reply })
        });
        
        const data = await response.json();
        
        if (data.success) {
            toastr.success(data.message);
            location.reload();
        } else {
            toastr.error(data.message || 'Failed to submit reply');
        }
    } catch (error) {
        toastr.error('Network error');
    }
}

async function updateReviewStatus(reviewId, status) {
    try {
        const response = await fetch(`/company/reviews/${reviewId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });
        
        const data = await response.json();
        
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error('Failed to update status');
        }
    } catch (error) {
        toastr.error('Network error');
    }
}

async function deleteReview(reviewId) {
    if (!confirm('{{ __("messages.confirm_delete_review") }}')) {
        return;
    }
    
    try {
        const response = await fetch(`/company/reviews/${reviewId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        if (response.ok) {
            toastr.success('Review deleted successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error('Failed to delete review');
        }
    } catch (error) {
        toastr.error('Network error');
    }
}
</script>
@endpush

<style>
.rating-stars {
    display: inline-flex;
    gap: 2px;
}

.status-badge.approved {
    background: #10b98120;
    color: #10b981;
}

.status-badge.pending {
    background: #f59e0b20;
    color: #f59e0b;
}

.status-badge.rejected {
    background: #ef444420;
    color: #ef4444;
}
</style>
@endsection