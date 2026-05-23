@php
    $currentUser = auth()->user();
    $displayName = $currentUser?->name ?? 'Pengguna';

    $initials = collect(explode(' ', trim($displayName)))
        ->filter()
        ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->take(2)
        ->implode('');

    $initials = $initials !== '' ? $initials : 'U';
    $roleLabel = $currentUser?->role?->label();
@endphp

<nav class="fixed inset-x-0 top-0 z-30 border-b border-emerald-100 bg-white/95 text-emerald-900 shadow-md backdrop-blur transition-[left] duration-300 ease-in-out lg:left-[var(--sidebar-width)]">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-800 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                @click="toggleSidebar()"
                :aria-expanded="sidebarOpen.toString()"
                aria-controls="dashboard-sidebar"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" class="size-5">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Menu</span>
            </button>

            <a href="{{ route('patients.index') }}" class="flex min-w-0 items-center gap-2 font-semibold tracking-wide text-emerald-900 transition hover:text-emerald-600">
                <span class="text-lg">Sistem RSUD</span>
            </a>
        </div>

        <div class="flex items-center gap-3">
            @auth
                <div
                    x-data="{ userMenuOpen: false }"
                    class="relative"
                    @keydown.escape.window="userMenuOpen = false"
                    @click.outside="userMenuOpen = false"
                >
                    <button
                        type="button"
                        class="inline-flex max-w-[18rem] items-center gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-left shadow-sm transition hover:border-emerald-200 hover:bg-emerald-100/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                        @click="userMenuOpen = !userMenuOpen"
                        :aria-expanded="userMenuOpen.toString()"
                        aria-haspopup="true"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-500 to-emerald-700 text-sm font-bold text-white shadow">
                            {{ $initials }}
                        </span>

                        <span class="hidden min-w-0 flex-col leading-tight sm:flex">
                            <span class="truncate text-sm font-semibold text-slate-800">{{ $displayName }}</span>
                            <span class="truncate text-xs text-slate-500">{{ $roleLabel ?? 'Pengguna' }}</span>
                        </span>

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            class="size-4 shrink-0 text-emerald-600 transition-transform duration-200"
                            :class="{ 'rotate-180': userMenuOpen }"
                        >
                            <path d="M6 9l6 6 6-6" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="userMenuOpen"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="scale-95 opacity-0"
                        x-transition:enter-end="scale-100 opacity-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="scale-100 opacity-100"
                        x-transition:leave-end="scale-95 opacity-0"
                        class="absolute right-0 top-full z-50 mt-2 w-40 origin-top-right rounded-2xl border border-emerald-100 bg-white py-2 text-emerald-800 shadow-xl"
                    >
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="w-full px-4 py-2 text-left text-sm font-medium text-rose-500 transition hover:bg-emerald-50 focus:bg-emerald-50 focus:outline-none"
                            >
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-2xl border border-emerald-200 px-4 py-2 text-sm font-semibold text-emerald-900 shadow-sm transition hover:bg-emerald-50">
                    Login
                </a>
            @endauth
        </div>
    </div>
</nav>
