<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#10b981">
    <meta name="description" content="MotoPeer - Ride together. Deliver together.">
    <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
    <title>MotoPeer</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-tap-target">
    <div id="app"></div>
</body>
</html>
