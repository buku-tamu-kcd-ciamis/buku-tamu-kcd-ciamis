@php
	$manifestHref = $manifestUrl ?? asset('manifest.webmanifest');
	$cssFile = public_path('css/filament-custom.css');
	$cssVersion = file_exists($cssFile) ? (string) filemtime($cssFile) : '1';
@endphp

<link rel="stylesheet" href="{{ asset('css/filament-custom.css') }}?v={{ $cssVersion }}">
<link rel="manifest" href="{{ $manifestHref }}">
<meta name="theme-color" content="#14532d">
<meta name="application-name" content="Buku Tamu KCD Ciamis">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Buku Tamu KCD Ciamis">
<link rel="apple-touch-icon" href="{{ asset('pwa/apple-touch-icon.png') }}">
