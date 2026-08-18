@props(['type', 'class' => 'w-16 h-16'])

<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" class="{{ $class }}" aria-hidden="true">
    @switch($type)
        @case('milk')
            <path d="M24 8h16v6l4 8v32a4 4 0 0 1-4 4H24a4 4 0 0 1-4-4V22l4-8V8Z" fill="#ffffff" stroke="#d9dce1" stroke-width="2"/>
            <rect x="24" y="8" width="16" height="5" rx="1.5" fill="#2f6fed"/>
            <rect x="20" y="26" width="24" height="5" fill="#2f6fed" opacity="0.15"/>
            <text x="32" y="46" font-size="8" font-weight="700" fill="#2f6fed" text-anchor="middle" font-family="sans-serif">MILK</text>
            @break

        @case('bread')
            <path d="M10 34c0-12 9-20 22-20s22 8 22 20-3 20-22 20-22-8-22-20Z" fill="#e3a24a"/>
            <path d="M10 34c0-12 9-20 22-20s22 8 22 20" fill="none" stroke="#c9863a" stroke-width="2"/>
            <path d="M18 20c3-6 8-9 14-9s11 3 14 9" fill="#f2c47e"/>
            <path d="M18 40q6 4 14 4t14-4" stroke="#c9863a" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M14 30q4 3 4 8" stroke="#c9863a" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M50 30q-4 3-4 8" stroke="#c9863a" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            @break

        @case('soda')
            <path d="M24 6h16l2 10-3 6v34a2 2 0 0 1-2 2H27a2 2 0 0 1-2-2V22l-3-6 2-10Z" fill="#3a2a20"/>
            <rect x="24" y="6" width="16" height="4" rx="1" fill="#c1272d"/>
            <rect x="21" y="28" width="22" height="14" rx="2" fill="#ffffff"/>
            <text x="32" y="38" font-size="7" font-weight="700" fill="#c1272d" text-anchor="middle" font-family="sans-serif">COLA</text>
            @break

        @case('chips')
            <path d="M16 18c0-4 3-6 6-6h20c3 0 6 2 6 6l4 30a6 6 0 0 1-6 7H18a6 6 0 0 1-6-7l4-30Z" fill="#ff8a1e"/>
            <path d="M16 18c0-4 3-6 6-6h20c3 0 6 2 6 6" fill="none" stroke="#e6720c" stroke-width="2"/>
            <ellipse cx="32" cy="34" rx="12" ry="9" fill="#ffd77a"/>
            <text x="32" y="37" font-size="7" font-weight="700" fill="#a8560a" text-anchor="middle" font-family="sans-serif">CHIPS</text>
            @break

        @case('spray')
            <rect x="20" y="20" width="20" height="34" rx="4" fill="#7fc4d1"/>
            <rect x="26" y="10" width="8" height="12" rx="1.5" fill="#6b7280"/>
            <path d="M34 12h8a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-8" fill="#4b5563"/>
            <rect x="23" y="30" width="14" height="10" rx="1.5" fill="#ffffff" opacity="0.85"/>
            @break

        @case('veggies')
            <path d="M12 34h40l-4 18a4 4 0 0 1-4 3H20a4 4 0 0 1-4-3l-4-18Z" fill="#c98a4b"/>
            <path d="M12 34h40" stroke="#a9702f" stroke-width="2"/>
            <circle cx="24" cy="28" r="7" fill="#e35d3a"/>
            <circle cx="38" cy="26" r="8" fill="#5aa85c"/>
            <circle cx="32" cy="32" r="6" fill="#f2b134"/>
            @break

        @default
            <rect x="14" y="14" width="36" height="36" rx="8" fill="#fde7d3"/>
    @endswitch
</svg>
