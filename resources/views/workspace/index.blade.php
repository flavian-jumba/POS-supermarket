<x-layouts.public title="Choose Workspace">
    <section class="mx-auto w-full max-w-2xl">
        <h1 class="text-3xl font-black tracking-tight">Choose Workspace</h1>
        <div class="mt-6 grid gap-3">
            @foreach ($memberships as $membership)
                <form method="POST" action="{{ route('workspace.store') }}">
                    @csrf
                    <input type="hidden" name="organization_id" value="{{ $membership->organization_id }}">
                    <button class="flex w-full items-center justify-between rounded-xl border border-[var(--pos-border)] bg-white px-5 py-4 text-left transition hover:border-[var(--pos-orange)]">
                        <span class="font-bold">{{ $membership->organization->name }}</span>
                        <span class="text-sm font-semibold text-[var(--pos-orange)]">{{ Illuminate\Support\Str::headline($membership->role) }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </section>
</x-layouts.public>
