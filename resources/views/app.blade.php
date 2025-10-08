<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#a259ff">
    <meta name="apple-mobile-web-app-title" content="DentalSync">
    <meta name="application-name" content="DentalSync">
    <title>DentalSync</title>
    <!-- Favicon optimizado para diferentes tamaños -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('diente-favicon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('diente-favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('diente-favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('diente-favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div id="app"></div>
</body>
</html>
