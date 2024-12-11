<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'OLX Price Alerts' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Styles and Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="text-gray-800 dark:text-gray-200">
<div class="min-h-screen bg-gray-100 dark:bg-gray-900 pt-18 {{ $mainClass ?? '' }}">
    {{ $slot }}
    <x-layout.footer/>
</div>
</body>
</html>
