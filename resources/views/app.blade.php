<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    
    <title>{{ config('app.name', 'CNPOS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">


    <!-- ✅ PWA Meta Tags -->
    <link rel="manifest" href="/manifest.json" crossorigin="use-credentials">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#0d6efd">
    <meta name="msapplication-TileColor" content="#0d6efd">
    
    <!-- Mobile App Capable -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="POS Toko">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="/icons/icon-192x192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead

    <!-- ✅ PERBAIKAN: Service Worker Registration dengan nama yang benar -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // ✅ Gunakan /serviceworker.js bukan /sw.js
                navigator.serviceWorker.register('/serviceworker.js')
                    .then(function(registration) {
                        console.log('✅ ServiceWorker registered successfully:', registration);
                        console.log('Scope:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('❌ ServiceWorker registration failed:', error);
                    });
            });
        } else {
            console.log('❌ Service Worker not supported in this browser');
        }
    </script>
</head>
<body class="font-sans antialiased">
    @inertia

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>