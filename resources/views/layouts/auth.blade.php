<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', current_locale()) }}">

<head>
    @include('layouts.auth.head')
    @vite('resources/js/app.js')
    @inertiaHead
    @cookieconsentscripts
   
</head>

<body>
    @routes
    @inertia
    @if (env('COOKIE_CONSENT',false) && !getCookieConsent())
    @cookieconsentview
    @endif

   
</body>

</html>