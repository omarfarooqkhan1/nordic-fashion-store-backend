<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Nordic Skin') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Preload critical assets -->
    <link rel="preload" href="/build/assets/index-{{ config('app.version', '1.0.0') }}.css" as="style">
    <link rel="preload" href="/build/assets/index-{{ config('app.version', '1.0.0') }}.js" as="script">

    <!-- Styles -->
    <link rel="stylesheet" href="/build/assets/index-{{ config('app.version', '1.0.0') }}.css">
</head>
<body>
    <div id="root"></div>

    <!-- Scripts -->
    <script src="/build/assets/index-{{ config('app.version', '1.0.0') }}.js"></script>
</body>
</html>
