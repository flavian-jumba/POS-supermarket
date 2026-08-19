<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'StanleyMat POS' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--pos-bg)] text-[var(--pos-text)] antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col px-6 py-6">
            <header class="mb-8 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--pos-orange)] text-lg font-black text-white">S</span>
                    <span class="text-lg font-bold">StanleyMat POS</span>
                </a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm font-semibold text-[var(--pos-text-secondary)] hover:text-[var(--pos-orange)]">Sign out</button>
                    </form>
                @endauth
            </header>

            {{ $slot }}
        </main>
    </body>
</html>
