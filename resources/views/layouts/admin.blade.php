<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Rekam Medis')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen bg-white text-slate-800">
    <div
        x-data="{
            sidebarOpen: window.matchMedia('(min-width: 1024px)').matches,
            get sidebarWidth() {
                return this.sidebarOpen ? '16rem' : '4rem';
            },
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
            },
            closeSidebar() {
                this.sidebarOpen = false;
            },
        }"
        x-bind:style="`--sidebar-width: ${sidebarWidth};`"
        class="min-h-screen"
    >
        @include('layouts.sidebar')
        @include('partials.admin-nav')

        <div class="min-h-screen pt-16 transition-[margin-left] duration-300 ease-in-out lg:ml-[var(--sidebar-width)]">
            <main class="mx-auto max-w-6xl px-4 pb-12 pt-4 [&>*:first-child]:mt-0">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
