<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', __('messages.home'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    @stack('styles')
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-latin' }}">

    <!-- Top Notification Bar -->
    @include('components.notification-bar')

    <!-- Navbar -->
    @include('components.navbar')

    <!-- Sidebar (if authenticated) -->
    @auth
        @include('components.sidebar')
    @endauth

    <!-- Main Content -->
    <main class="main-content @auth has-sidebar @endauth">
        @include('components.flash-messages')
        @yield('content')
    </main>

    <!-- Footer -->
    @include('components.footer')

    <!-- Notification Dropdown Panel -->
    @auth
        @include('components.notification-panel')
    @endauth

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @stack('scripts')

    <!-- Flash Notifications via JS -->
    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "{{ app()->getLocale() === 'ar' ? 'toast-bottom-left' : 'toast-bottom-right' }}",
            "timeOut": "4000"
        };
        @if(session('success'))
            toastr.success("{{ session('success') }}");
        @endif
        @if(session('error'))
            toastr.error("{{ session('error') }}");
        @endif
        @if(session('warning'))
            toastr.warning("{{ session('warning') }}");
        @endif
        @if(session('info'))
            toastr.info("{{ session('info') }}");
        @endif
    </script>
</body>
</html>