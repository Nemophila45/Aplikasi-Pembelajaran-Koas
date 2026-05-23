@php
    use App\Enums\UserRole;
    use Illuminate\Support\Facades\Route;

    $role = auth()->user()?->role;
    $historyUrl = Route::has('admin.history.index') ? route('admin.history.index') : '#';
    $diseaseChartUrl = Route::has('admin.reports.disease-chart') ? route('admin.reports.disease-chart') : '#';
@endphp

<div class="relative">
    <div
        x-show="sidebarOpen"
        x-cloak
        x-transition.opacity
        @click="closeSidebar()"
        class="fixed inset-0 z-40 bg-slate-950/40 backdrop-blur-[2px] lg:hidden"
        aria-hidden="true"
    ></div>

    <aside
        id="dashboard-sidebar"
        class="fixed inset-y-0 left-0 z-50 flex h-screen flex-col overflow-y-auto bg-emerald-900 text-white shadow-xl transition-[width,transform] duration-300 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        :style="`width: ${sidebarWidth};`"
    >
        <div class="flex items-center gap-2 px-3 py-4">
            <div class="flex items-center gap-2">
                <span
                    class="font-semibold tracking-wide"
                    x-show="sidebarOpen"
                    x-transition
                    x-cloak
                >
                    Sistem RSUD
                </span>
            </div>
        </div>

        <nav class="mt-4 flex-1 space-y-1 px-2">
            @if ($role === UserRole::ADMIN)
                <div class="space-y-1">
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium hover:bg-white/10"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5">
                                    <path d="M7 7h10M7 12h10M7 17h6" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                        <span x-show="sidebarOpen" x-transition x-cloak>Kelola Akun</span>
                        </a>

                    @if ($historyUrl !== '#')
                        <a
                            href="{{ $historyUrl }}"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium hover:bg-white/10"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5">
                                    <path d="M12 6v6l3 3" stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="8" />
                                </svg>
                            </span>
                            <div x-show="sidebarOpen" x-transition x-cloak class="leading-tight">
                                <div>History</div>
                            </div>
                        </a>
                    @endif

                    @if ($diseaseChartUrl !== '#')
                        <a
                            href="{{ $diseaseChartUrl }}"
                            @class([
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium hover:bg-white/10',
                                request()->routeIs('admin.reports.disease-chart') ? 'bg-white/10' : '',
                            ])
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5">
                                    <path d="M4 19v-6m5 6V5m5 14V9m5 10V3" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span x-show="sidebarOpen" x-transition x-cloak>Grafik Penyakit</span>
                        </a>
                    @endif
                </div>
            @elseif ($role === UserRole::MANAGEMENT)
                <div class="space-y-1">
                    @if ($diseaseChartUrl !== '#')
                        <a
                            href="{{ $diseaseChartUrl }}"
                            @class([
                                'flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium hover:bg-white/10',
                                request()->routeIs('admin.reports.disease-chart') ? 'bg-white/10' : '',
                            ])
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/10">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="size-5">
                                    <path d="M4 19v-6m5 6V5m5 14V9m5 10V3" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            <span x-show="sidebarOpen" x-transition x-cloak>Grafik Penyakit</span>
                        </a>
                    @endif
                </div>
            @endif
        </nav>

    </aside>
</div>
