<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    @fonts
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            body { font-family: system-ui, sans-serif; }
        </style>
    @endif
    @stack('head')
</head>
<body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
    <div class="flex min-h-screen flex-col">
        <header class="border-b border-zinc-200/80 bg-white/80 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/80">
            <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4 sm:px-6">
                <a href="{{ route('home') }}" class="text-sm font-semibold tracking-tight">{{ config('app.name') }}</a>
                <nav class="flex items-center gap-3 text-sm">
                    @yield('nav')
                </nav>
            </div>
        </header>
        <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-10 sm:px-6">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
