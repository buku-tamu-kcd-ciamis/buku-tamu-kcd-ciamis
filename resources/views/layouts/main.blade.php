<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('seo.default_title', 'Buku Tamu KCD Ciamis'))</title>
    @include('seo.meta')
    <link rel="icon" href="{{ asset('img/logo-cadisdik.png') }}" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @stack('seo')
    @stack('styles')
</head>
<body>

    @yield('content')

    @stack('scripts')
</body>
</html>
