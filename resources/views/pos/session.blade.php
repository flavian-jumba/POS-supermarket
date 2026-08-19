<x-layouts.public title="Select Register">
    <section class="mx-auto w-full max-w-2xl rounded-2xl border border-[var(--pos-border)] bg-white p-8 shadow-sm">
        <h1 class="text-3xl font-black tracking-tight">Select Register</h1>
        <p class="mt-2 text-sm text-[var(--pos-text-secondary)]">{{ $organization->name }}</p>

        <form method="POST" action="{{ route('pos.session.store') }}" class="mt-8 grid gap-5">
            @csrf
            <label class="grid gap-2">
                <span class="text-sm font-bold">Register</span>
                <select name="register_id" class="h-12 rounded-xl border border-[var(--pos-border)] bg-white px-4 outline-none focus:border-[var(--pos-orange)]">
                    @foreach ($registers as $register)
                        <option value="{{ $register->id }}">{{ $register->branch->name }} · {{ $register->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Opening Cash</span>
                <input name="opening_cash" type="number" min="0" step="0.01" value="{{ old('opening_cash', '0.00') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
            </label>
            <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white">Start Shift</button>
        </form>
    </section>
</x-layouts.public>
