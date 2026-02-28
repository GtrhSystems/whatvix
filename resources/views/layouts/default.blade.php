<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', current_locale()) }}" data-bs-theme="light">

<head>
    <link rel="icon" type="image/png" sizes="56x56" href="{{ asset(get_option('primary_data.favicon')) }}">
    @include('layouts.default.meta')
    @include('layouts.default.stylesheets')
    @vite('resources/js/app.js')
    @inertiaHead
    @cookieconsentscripts
</head>

<body>
    @routes
    @inertia
    @if (env('COOKIE_CONSENT', false) && !getCookieConsent())
        @cookieconsentview
    @endif

        <script src="{{ asset('assets/frontend/vendor/jquery.min.js') }}">
        </script>
        <script src="{{ asset('assets/frontend/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>

</html>