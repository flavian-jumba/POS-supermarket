<x-layouts.public title="Sign In">
    <section class="mx-auto w-full max-w-md rounded-2xl border border-[var(--pos-border)] bg-white p-8 shadow-sm">
        <h1 class="text-3xl font-black tracking-tight">Sign in</h1>
        <form method="POST" action="{{ route('login.store') }}" class="mt-8 grid gap-5">
            @csrf
            <label class="grid gap-2">
                <span class="text-sm font-bold">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
                @error('email') <span class="text-sm text-[var(--pos-danger)]">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Password</span>
                <input type="password" name="password" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
            </label>
            <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white transition hover:bg-[var(--pos-orange-hover)]">Sign In</button>
        </form>
    </section>
</x-layouts.public>
