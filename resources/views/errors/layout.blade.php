<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', current_locale()) }}" data-bs-theme="dark">

<head>
	<meta charset="UTF-8">
	<meta property="og:site_name" content="@yield('title')">
	<meta property="og:url" content="{{ url('/') }}">
	<meta property="og:type" content="website">
	<meta property="og:title" content="@yield('title')">

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
	<title>@yield('title')</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" sizes="56x56"
		href="{{ asset(get_option('primary_data')['favicon'] ?? '/assets/frontend/images/fav-icon/icon.png') }}">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/css/bootstrap.min.css') }}" media="all">
	<!-- Main style sheet -->
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/css/style.min.css') }}" media="all">
	<!-- responsive style sheet -->
	<link rel="stylesheet" type="text/css" href="{{ asset('assets/frontend/css/responsive.css') }}" media="all">

	
</head>

<body>
	<div class="main-page-wrapper">
	

		<!--
		=====================================================
			Error Page
		=====================================================
		-->
		<div class="error-page d-flex align-items-center justify-content-center">
			<div class="inner-wrapper">
				<h2>@yield('code')</h2>
				<h3>@yield('message')</h3>

				<a href="{{ url('/') }}" class="btn-thirteen mt-35"><span class="">{{ __('Back to home') }}</span></a>
			</div>
		</div>
		<!-- /.error-page -->





		<!-- Optional JavaScript _____________________________  -->

		<!-- jQuery first, then Bootstrap JS -->
		<!-- jQuery -->
		<script src="{{ asset('assets/frontend/vendor/jquery.min.js') }}"></script>
		<!-- Bootstrap JS -->
		<script src="{{ asset('assets/frontend/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>



		<!-- Theme js -->
		<script src="{{ asset('assets/frontend/js/theme.js') }}"></script>
	</div> <!-- /.main-page-wrapper -->
</body>

</html>