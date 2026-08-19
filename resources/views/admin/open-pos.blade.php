<x-layouts.public title="Open POS">
    <section class="mx-auto w-full max-w-2xl rounded-2xl border border-[var(--pos-border)] bg-white p-8 shadow-sm">
        <h1 class="text-3xl font-black tracking-tight">Open POS</h1>
        <p class="mt-2 text-sm text-[var(--pos-text-secondary)]">{{ $organization->name }}</p>

        <form method="POST" action="{{ route('admin.open-pos.store') }}" class="mt-8 grid gap-5">
            @csrf
            <label class="grid gap-2">
                <span class="text-sm font-bold">Branch</span>
                <select name="branch_id" class="h-12 rounded-xl border border-[var(--pos-border)] bg-white px-4 outline-none focus:border-[var(--pos-orange)]">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2">
                <span class="text-sm font-bold">Register</span>
                <select name="register_id" class="h-12 rounded-xl border border-[var(--pos-border)] bg-white px-4 outline-none focus:border-[var(--pos-orange)]">
                    @foreach ($branches as $branch)
                        @foreach ($branch->registers as $register)
                            <option value="{{ $register->id }}">{{ $branch->name }} · {{ $register->name }}</option>
                        @endforeach
                    @endforeach
                </select>
            </label>
            <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white">Open POS</button>
        </form>
    </section>
</x-layouts.public>
