<x-layouts.public title="Register Supermarket">
    <section class="mx-auto w-full max-w-2xl rounded-2xl border border-[var(--pos-border)] bg-white p-8 shadow-sm">
        <h1 class="text-3xl font-black tracking-tight">Create your supermarket</h1>
        <p class="mt-2 text-sm text-[var(--pos-text-secondary)]">Start with the owner account and supermarket workspace.</p>

        <form method="POST" action="{{ route('register.store') }}" class="mt-8 grid gap-5 md:grid-cols-2">
            @csrf
            <label class="grid gap-2 md:col-span-2">
                <span class="text-sm font-bold">Supermarket Name</span>
                <input name="supermarket_name" value="{{ old('supermarket_name') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
                @error('supermarket_name') <span class="text-sm text-[var(--pos-danger)]">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Owner / Administrator Name</span>
                <input name="name" value="{{ old('name') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
                @error('name') <span class="text-sm text-[var(--pos-danger)]">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
                @error('email') <span class="text-sm text-[var(--pos-danger)]">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Phone</span>
                <input name="phone" value="{{ old('phone') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
                @error('phone') <span class="text-sm text-[var(--pos-danger)]">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Password</span>
                <input type="password" name="password" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
                @error('password') <span class="text-sm text-[var(--pos-danger)]">{{ $message }}</span> @enderror
            </label>
            <label class="grid gap-2 md:col-span-2">
                <span class="text-sm font-bold">Confirm Password</span>
                <input type="password" name="password_confirmation" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]" required>
            </label>
            <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white transition hover:bg-[var(--pos-orange-hover)] md:col-span-2">Create Supermarket</button>
        </form>
    </section>
</x-layouts.public>
