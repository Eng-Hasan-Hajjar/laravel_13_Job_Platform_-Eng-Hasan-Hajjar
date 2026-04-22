{{-- resources/views/components/notification-bar.blade.php --}}
{{-- Only show if there's a system announcement --}}
@php $announcement = cache('system_announcement'); @endphp
@if($announcement)
<div class="notification-bar" id="announcementBar">
    <i class="fas fa-bullhorn"></i>
    <span>{{ $announcement }}</span>
    <button onclick="document.getElementById('announcementBar').remove()"
            style="background:none;border:none;color:white;cursor:pointer;margin-left:.75rem;opacity:.8;font-size:1rem">×</button>
</div>
@endif