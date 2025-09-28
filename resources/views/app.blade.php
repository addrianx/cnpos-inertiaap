<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <!-- Lock zoom & responsive -->
    <meta 
        name="viewport" 
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"
    >

    <title inertia>{{ config('app.name', 'CNPOS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- PWA manifest -->
    <link rel="manifest" href="/manifest.json">

    <!-- Android: status bar & full screen -->
    <meta name="theme-color" content="#0d6efd">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="{{ config('app.name', 'Laravel') }}">

    <!-- iOS: status bar & full screen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Laravel') }}">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">

    <!-- Windows / Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="#0d6efd">
    <meta name="msapplication-TileImage" content="/images/icons/icon-192x192.png">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
