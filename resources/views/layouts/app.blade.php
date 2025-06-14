<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    {{-- Menghilangkan CDN Bootstrap karena sudah diimport di app.js --}}
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @stack('styles')
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .content {
            margin-top: 0 !important;
        }
    </style>
</head>
<body>
    {{-- Removed the default navbar as a custom one is used in specific views --}}

    @yield('content')

    {{-- Menghilangkan CDN Bootstrap dan jQuery karena sudah diimport di app.js --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> --}}
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

    {{-- Memuat aset JavaScript yang dibundle oleh Vite --}}
    @vite(['resources/js/app.js'])

    @stack('scripts')
</body>
</html>
