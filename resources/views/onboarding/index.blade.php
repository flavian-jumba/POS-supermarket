<x-layouts.public title="Onboarding">
    <section class="mx-auto w-full max-w-3xl rounded-2xl border border-[var(--pos-border)] bg-white p-8 shadow-sm">
        <div class="mb-8 flex flex-wrap items-center gap-3">
            @foreach (['business' => 'Business', 'branch' => 'First Branch', 'register' => 'First Register', 'complete' => 'Complete'] as $key => $label)
                <span @class([
                    'rounded-full px-4 py-2 text-xs font-black',
                    'bg-[var(--pos-orange)] text-white' => $step === $key,
                    'bg-[var(--pos-orange-soft)] text-[var(--pos-orange)]' => $step !== $key,
                ])>{{ $label }}</span>
            @endforeach
        </div>

        @if ($step === 'business')
            <h1 class="text-3xl font-black tracking-tight">Business details</h1>
            <form method="POST" action="{{ route('onboarding.business') }}" class="mt-8 grid gap-5 md:grid-cols-2">
                @csrf
                <label class="grid gap-2 md:col-span-2">
                    <span class="text-sm font-bold">Supermarket Name</span>
                    <input name="name" value="{{ old('name', $organization->name) }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <label class="grid gap-2">
                    <span class="text-sm font-bold">Email</span>
                    <input type="email" name="email" value="{{ old('email', $organization->email) }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <label class="grid gap-2">
                    <span class="text-sm font-bold">Phone</span>
                    <input name="phone" value="{{ old('phone', $organization->phone) }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white md:col-span-2">Continue</button>
            </form>
        @elseif ($step === 'branch')
            <h1 class="text-3xl font-black tracking-tight">Create first branch</h1>
            <form method="POST" action="{{ route('onboarding.branch') }}" class="mt-8 grid gap-5 md:grid-cols-2">
                @csrf
                <label class="grid gap-2">
                    <span class="text-sm font-bold">Branch Name</span>
                    <input name="name" value="{{ old('name', 'Main Branch') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <label class="grid gap-2">
                    <span class="text-sm font-bold">Branch Code</span>
                    <input name="code" value="{{ old('code', 'MAIN') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <label class="grid gap-2 md:col-span-2">
                    <span class="text-sm font-bold">Address</span>
                    <input name="address" value="{{ old('address') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <label class="grid gap-2 md:col-span-2">
                    <span class="text-sm font-bold">Phone</span>
                    <input name="phone" value="{{ old('phone', $organization->phone) }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white md:col-span-2">Create Branch</button>
            </form>
        @elseif ($step === 'register')
            <h1 class="text-3xl font-black tracking-tight">Create first register</h1>
            <form method="POST" action="{{ route('onboarding.register') }}" class="mt-8 grid gap-5 md:grid-cols-2">
                @csrf
                <label class="grid gap-2">
                    <span class="text-sm font-bold">Register Name</span>
                    <input name="name" value="{{ old('name', 'Register 01') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <label class="grid gap-2">
                    <span class="text-sm font-bold">Register Code</span>
                    <input name="code" value="{{ old('code', 'REG-01') }}" class="h-12 rounded-xl border border-[var(--pos-border)] px-4 outline-none focus:border-[var(--pos-orange)]">
                </label>
                <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white md:col-span-2">Create Register</button>
            </form>
        @else
            <h1 class="text-3xl font-black tracking-tight">Ready to open the dashboard</h1>
            <div class="mt-6 grid gap-3 text-sm font-semibold">
                <div>✓ Create supermarket</div>
                <div>✓ Create first branch</div>
                <div>✓ Create first register</div>
            </div>
            <form method="POST" action="{{ route('onboarding.complete') }}" class="mt-8">
                @csrf
                <button class="rounded-xl bg-[var(--pos-orange)] px-6 py-4 text-sm font-black text-white">Enter Dashboard</button>
            </form>
        @endif
    </section>
</x-layouts.public>
