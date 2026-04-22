{{-- resources/views/components/flash-messages.blade.php --}}
@if(session('success'))
<div class="alert alert-success animate-slide-up" style="margin:1rem 1.5rem 0;border-radius:var(--radius)">
    <i class="fas fa-check-circle"></i>
    <span>{{ session('success') }}</span>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;color:currentColor;opacity:.7;font-size:1rem">×</button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger animate-slide-up" style="margin:1rem 1.5rem 0;border-radius:var(--radius)">
    <i class="fas fa-exclamation-circle"></i>
    <span>{{ session('error') }}</span>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;color:currentColor;opacity:.7;font-size:1rem">×</button>
</div>
@endif
@if(session('warning'))
<div class="alert alert-warning animate-slide-up" style="margin:1rem 1.5rem 0;border-radius:var(--radius)">
    <i class="fas fa-exclamation-triangle"></i>
    <span>{{ session('warning') }}</span>
    <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;margin-left:auto;color:currentColor;opacity:.7;font-size:1rem">×</button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger animate-slide-up" style="margin:1rem 1.5rem 0;border-radius:var(--radius);flex-direction:column;align-items:flex-start">
    <div style="display:flex;align-items:center;gap:.5rem;font-weight:700;margin-bottom:.375rem">
        <i class="fas fa-exclamation-circle"></i>
        {{ __('messages.please_fix_errors') }}
    </div>
    <ul style="list-style:none;padding:0;margin:0;font-size:.875rem">
        @foreach($errors->all() as $error)
        <li style="padding:.125rem 0">• {{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif