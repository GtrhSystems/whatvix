@php
    use App\Helpers\SeoMeta;
@endphp

<meta charset="utf-8">
<!-- For IE -->
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<!-- For Resposive Device -->
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- For Window Tab Color -->
<!-- Chrome, Firefox OS and Opera -->
<meta name="theme-color" content="#0D1A1C">
<!-- Windows Phone -->
<meta name="msapplication-navbutton-color" content="#0D1A1C">
<!-- iOS Safari -->
<meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="app-name" content="{{ config('app.name') }}" />
<meta name="app-translations" content="{{ getTranslationFile() }}" />

<title inertia>{{ SeoMeta::get('title') }} - {{ config('app.name') }}</title>

<meta name="description" itemprop="description" inertia content="{{ SeoMeta::get('description') }}" />
<meta name="keywords" inertia content="{{ SeoMeta::get('keywords') }}" />
<meta property="og:description" inertia content="{{ SeoMeta::get('description') }}" />
<meta property="og:title" inertia content="{{ SeoMeta::get('title') }}" />
<meta property="og:url" inertia content="{{ request()->fullUrl() }}" />

<meta property="og:site_name" inertia content="{{ SeoMeta::get('title') }}" />
<meta property="og:image" inertia content="{{ asset(SeoMeta::get('preview')) }}" />
<meta property="og:image:url" inertia content="{{ asset(SeoMeta::get('preview')) }}" />

<meta name="twitter:card" inertia content="{{ SeoMeta::get('description') }}" />
<meta name="twitter:title" inertia content="{{ SeoMeta::get('title') }}" />