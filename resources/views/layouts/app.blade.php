@php
    $isRtl = app()->getLocale() === 'ar';
    $pageTitle = $title ?? trim($__env->yieldContent('page-title')) ?: 'Dashboard';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __($pageTitle) }} – {{ $appDisplayName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-100 antialiased {{ $isRtl ? 'font-[Tajawal]' : 'font-[Inter]' }}" x-data="{ sidebarOpen: true, mobileSidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
        class="hidden lg:flex flex-col bg-slate-900 text-white transition-all duration-300 ease-in-out flex-shrink-0">

        {{-- Brand --}}
        <div class="flex items-center h-16 px-4 border-b border-slate-700 flex-shrink-0">
            <div class="flex items-center justify-center w-9 h-9 bg-blue-600 rounded-xl flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <span x-show="sidebarOpen" x-transition class="{{ $isRtl ? 'mr-3' : 'ml-3' }} font-bold text-lg tracking-tight">{{ $appDisplayName }}</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-2 text-sm">

            @include('layouts.partials.nav-item', ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard', 'permission' => 'view dashboard'])

            @can('create sales')
            @include('layouts.partials.nav-item', ['route' => 'pos', 'icon' => 'shopping-cart', 'label' => 'POS / Checkout', 'permission' => 'create sales'])
            @endcan

            {{-- Sales --}}
            @can('view sales')
            @include('layouts.partials.nav-item', ['route' => 'sales.index', 'icon' => 'receipt-refund', 'label' => 'Sales', 'permission' => 'view sales'])
            @endcan

            {{-- Products --}}
            @can('view products')
            @include('layouts.partials.nav-item', ['route' => 'products.index', 'icon' => 'cube', 'label' => 'Products', 'permission' => 'view products'])
            @endcan

            @can('view categories')
            @include('layouts.partials.nav-item', ['route' => 'categories.index', 'icon' => 'tag', 'label' => 'Categories', 'permission' => 'view categories'])
            @endcan

            {{-- Inventory --}}
            @can('view inventory')
            @include('layouts.partials.nav-item', ['route' => 'inventory.index', 'icon' => 'archive-box', 'label' => 'Inventory', 'permission' => 'view inventory'])
            @endcan

            {{-- Purchases --}}
            @can('view purchases')
            @include('layouts.partials.nav-item', ['route' => 'purchases.index', 'icon' => 'truck', 'label' => 'Purchases', 'permission' => 'view purchases'])
            @endcan

            @can('view suppliers')
            @include('layouts.partials.nav-item', ['route' => 'suppliers.index', 'icon' => 'building-office', 'label' => 'Suppliers', 'permission' => 'view suppliers'])
            @endcan

            {{-- Customers --}}
            @can('view customers')
            @include('layouts.partials.nav-item', ['route' => 'customers.index', 'icon' => 'users', 'label' => 'Customers', 'permission' => 'view customers'])
            @endcan

            {{-- Discounts --}}
            @can('view discounts')
            @include('layouts.partials.nav-item', ['route' => 'discounts.index', 'icon' => 'ticket', 'label' => 'Discounts', 'permission' => 'view discounts'])
            @endcan

            {{-- Expenses --}}
            @can('view expenses')
            @include('layouts.partials.nav-item', ['route' => 'expenses.index', 'icon' => 'banknotes', 'label' => 'Expenses', 'permission' => 'view expenses'])
            @endcan

            {{-- Reports --}}
            @can('view reports')
            @include('layouts.partials.nav-item', ['route' => 'reports.index', 'icon' => 'chart-bar', 'label' => 'Reports', 'permission' => 'view reports'])
            @endcan

            {{-- Admin --}}
            @can('view users')
            <div x-show="sidebarOpen" class="px-3 pt-4 pb-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Admin') }}</span>
            </div>
            @include('layouts.partials.nav-item', ['route' => 'users.index', 'icon' => 'user-group', 'label' => 'Users', 'permission' => 'view users'])
            @endcan

            @can('view branches')
            @include('layouts.partials.nav-item', ['route' => 'branches.index', 'icon' => 'building-storefront', 'label' => 'Branches', 'permission' => 'view branches'])
            @endcan

            @can('manage settings')
            @include('layouts.partials.nav-item', ['route' => 'settings', 'icon' => 'cog-6-tooth', 'label' => 'Settings', 'permission' => 'manage settings'])
            @endcan

        </nav>

        {{-- Collapse Button --}}
        <div class="border-t border-slate-700 p-3">
            <button @click="sidebarOpen = !sidebarOpen"
                class="flex items-center gap-2 w-full px-2 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-700 transition-colors text-sm">
                <svg class="w-5 h-5 flex-shrink-0 transition-transform" :class="sidebarOpen ? '' : 'rotate-180'"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
                <span x-show="sidebarOpen" x-transition class="text-sm">{{ __('Collapse') }}</span>
            </button>
        </div>
    </aside>

    {{-- Mobile Sidebar Overlay --}}
    <div x-show="mobileSidebarOpen" @click="mobileSidebarOpen = false"
        class="fixed inset-0 z-20 bg-black/50 lg:hidden"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    </div>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="bg-white border-b border-slate-200 h-16 flex items-center px-4 gap-4 flex-shrink-0 shadow-sm">
            <button @click="mobileSidebarOpen = !mobileSidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1">
                <h1 class="text-lg font-semibold text-slate-800">{{ __($pageTitle) }}</h1>
                @hasSection('breadcrumbs')
                <div class="text-xs text-slate-500 mt-0.5">@yield('breadcrumbs')</div>
                @endif
            </div>

            {{-- Right Actions --}}
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('locale.switch') }}" class="hidden sm:block">
                    @csrf
                    <label for="locale-switcher" class="sr-only">{{ __('Language') }}</label>
                    <select id="locale-switcher" name="locale" onchange="this.form.submit()"
                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                        @foreach(config('app.supported_locales', []) as $localeCode => $localeLabel)
                            <option value="{{ $localeCode }}" @selected(app()->getLocale() === $localeCode)>{{ $localeLabel }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Branch Indicator --}}
                <div class="hidden sm:flex items-center gap-1.5 text-sm text-slate-600 bg-slate-100 px-3 py-1.5 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span>{{ auth()->user()->branch?->name ?? __('No Branch') }}</span>
                </div>

                {{-- User Menu --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-10">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ __('Profile') }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                {{ __('Sign Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto">
            {{-- Flash Messages --}}
            @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="mx-6 mt-4 flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm shadow-sm">
                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('success') }}
                <button @click="show = false" class="ml-auto text-green-600 hover:text-green-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @endif

            @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="mx-6 mt-4 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm shadow-sm">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            <div class="p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

@livewireScripts
</body>
</html>
