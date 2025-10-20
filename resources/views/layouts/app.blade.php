<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="profiler">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Profiler') }} - Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-base-200">
    <div class="flex h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-base-100 border-r border-base-300 flex flex-col">
            @livewire('sidebar')
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 flex flex-col overflow-hidden">
            {{-- Header --}}
            <header class="bg-base-100 border-b border-base-300 px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        @if(isset($pageCategory))
                            <div class="text-xs text-base-content/60 uppercase tracking-wider font-medium mb-2">{{ $pageCategory }}</div>
                        @else
                            <div class="text-xs text-base-content/60 uppercase tracking-wider font-medium mb-2">Ankole Person Profiler</div>
                        @endif

                        @if(isset($pageTitle))
                            <h1 class="text-3xl font-bold text-base-content mb-2">{{ $pageTitle }}</h1>
                        @else
                            <h1 class="text-3xl font-bold text-base-content mb-2">Welcome to Person Registry</h1>
                        @endif

                        @if(isset($pageSubtitle))
                            <p class="text-base-content/70 text-sm max-w-2xl">{{ $pageSubtitle }}</p>
                        @else
                            {{-- <p class="text-base-content/70 text-sm max-w-2xl">
                                Comprehensive person identity management system with role-based access, organizational
                                affiliations.
                            </p> --}}
                        @endif
                    </div>
                    <div class="flex items-center gap-3" id='topbar-menu'>
                        <x-topbar-menu />
                    </div>
                </div>
            </header>

            <!-- Page Content -->
           <div class="flex-1 h-[calc(100vh-8rem)] overflow-y-auto">
    @isset($slot)
        {{ $slot }} {{-- For Blade component usage --}}
    @else
        @yield('content') {{-- For @extends layout usage --}}
    @endisset
</div>
        </main>
        @livewireScripts

    </div>

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }

        // Load saved theme
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    {{-- Sweet Alert Component for Error Handling --}}
    <x-sweet-alerts />
</body>

</html>
