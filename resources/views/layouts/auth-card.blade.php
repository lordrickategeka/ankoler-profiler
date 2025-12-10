<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="profiler">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - {{ $title ?? '' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --p: 152, 43, 85 !important;
            --primary: #982B55 !important;
        }
        .btn-primary,
        .badge-primary,
        .bg-primary,
        .text-primary {
            --tw-bg-opacity: 1;
            background-color: #982B55 !important;
            color: #fff !important;
            border-color: #982B55 !important;
        }
        .border-primary {
            border-color: #982B55 !important;
        }
        .text-primary {
            color: #982B55 !important;
        }
    </style>
</head>
<body class="min-h-screen bg-base-200" style="background: url('{{ asset('images/loginbackground.jpg') }}') center center / contain no-repeat fixed; background-color: #fff;">
    <div class="min-h-screen flex items-center justify-center">
        <div class="card card-lg w-full max-w-4xl md:max-w-5xl shadow-2xl bg-base-100 relative px-2 md:px-8">
            <div class="flex flex-col items-center mt-8">
                <img src="/images/Ankole-Diocese-Logo.png" alt="Ankole Diocese Logo" class="object-contain mb-4" style="height: 5rem; width: auto;" />
            </div>
            <div class="card-body">
                @isset($slot)
        {{ $slot }} {{-- For Blade component usage --}}
    @else
        @yield('content') {{-- For @extends layout usage --}}
    @endisset
            </div>
        </div>
    </div>
    {{-- <footer class="footer footer-center p-4 bg-base-200 text-base-content absolute bottom-0 w-full">
        <aside>
            <p>&copy; {{ date('Y') }} Ankole Diocese Profiler Portal. All rights reserved.</p>
        </aside>
    </footer> --}}
    @livewireScripts
</body>
</html>
