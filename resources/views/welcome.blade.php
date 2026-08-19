<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>StanleyMat POS</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[var(--pos-bg)] text-[var(--pos-text)] antialiased">
        <main class="mx-auto flex min-h-screen w-full max-w-6xl flex-col px-6 py-6">
            <header class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--pos-orange)] text-lg font-black text-white">S</span>
                    <span class="text-lg font-bold">StanleyMat POS</span>
                </div>
                <a href="{{ route('login') }}" class="rounded-xl border border-[var(--pos-border)] bg-white px-5 py-3 text-sm font-semibold text-[var(--pos-text)] transition hover:border-[var(--pos-orange)] hover:text-[var(--pos-orange)]">Sign In</a>
            </header>

            <section class="grid flex-1 items-center gap-12 py-14 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="max-w-2xl">
                    <p class="mb-5 text-sm font-bold uppercase tracking-[0.18em] text-[var(--pos-orange)]">Supermarket operations</p>
                    <h1 class="text-5xl font-black leading-tight tracking-tight md:text-6xl">Run every branch, register, product and sale in one clean POS.</h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-[var(--pos-text-secondary)]">
                        Register your supermarket, set up your first branch and register, then manage catalogue, stock, staff and cashier sales from one isolated workspace.
                    </p>
                    <div class="mt-9 flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" class="rounded-xl bg-[var(--pos-orange)] px-7 py-4 text-sm font-bold text-white shadow-sm transition hover:bg-[var(--pos-orange-hover)]">Get Started</a>
                        <a href="{{ route('login') }}" class="rounded-xl border border-[var(--pos-border)] bg-white px-7 py-4 text-sm font-bold text-[var(--pos-text)] transition hover:border-[var(--pos-orange)] hover:text-[var(--pos-orange)]">Sign In</a>
                    </div>
                </div>

                <div class="rounded-2xl border border-[var(--pos-border)] bg-white p-5 shadow-sm">
                    <div class="rounded-xl bg-[var(--pos-bg)] p-5">
                        <div class="mb-5 flex items-center justify-between">
                            <span class="text-sm font-bold">Today</span>
                            <span class="rounded-full bg-[var(--pos-orange-soft)] px-3 py-1 text-xs font-bold text-[var(--pos-orange)]">Live POS</span>
                        </div>
                        <div class="grid gap-3">
                            @foreach ([['Sales', 'KSh 128,450'], ['Open Registers', '4'], ['Low Stock Lines', '8']] as [$label, $value])
                                <div class="flex items-center justify-between rounded-xl border border-[var(--pos-border)] bg-white px-5 py-4">
                                    <span class="text-sm font-medium text-[var(--pos-text-secondary)]">{{ $label }}</span>
                                    <span class="text-lg font-black">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
