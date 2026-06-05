<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', __('messages.home'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sidebar-fix.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @stack('styles')
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-latin' }}">

    @include('components.notification-bar')
    @include('components.navbar')

    @auth
        @include('components.sidebar')
    @endauth

    <main class="main-content @auth has-sidebar @endauth">
        @include('components.flash-messages')
        @yield('content')
    </main>

    {{-- الفوتر يظهر فقط للزوّار غير المسجّلين (لا سايدبار). أي مستخدم مسجّل = سايدبار = بدون فوتر --}}
    @guest
        @include('components.footer')
    @endguest

    @auth
        @include('components.notification-panel')
    @endauth

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')

    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "{{ app()->getLocale() === 'ar' ? 'toast-bottom-left' : 'toast-bottom-right' }}",
            "timeOut": "4000"
        };
        @if(session('success'))  toastr.success("{{ addslashes(session('success')) }}");  @endif
        @if(session('error'))    toastr.error("{{ addslashes(session('error')) }}");      @endif
        @if(session('warning'))  toastr.warning("{{ addslashes(session('warning')) }}");  @endif
        @if(session('info'))     toastr.info("{{ addslashes(session('info')) }}");        @endif
    </script>
</body>
</html>