@php
    $isRtl = app()->getLocale() === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.display_name', 'WehancePOS') }} - {{ __('Create Account') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 flex items-center justify-center p-4 {{ $isRtl ? 'font-[Tajawal]' : 'font-[Inter]' }}">

<div class="w-full max-w-md">
    <div class="mb-4 flex justify-end">
        <form method="POST" action="{{ route('locale.switch') }}">
            @csrf
            <label for="register-locale-switcher" class="sr-only">{{ __('Language') }}</label>
            <select id="register-locale-switcher" name="locale" onchange="this.form.submit()"
                class="rounded-lg border border-white/20 bg-white/10 px-3 py-2 text-sm text-white focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200/20">
                @foreach(config('app.supported_locales', []) as $localeCode => $localeLabel)
                    <option value="{{ $localeCode }}" @selected(app()->getLocale() === $localeCode) class="text-slate-900">{{ $localeLabel }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-4 shadow-xl">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">{{ config('app.display_name', 'WehancePOS') }}</h1>
        <p class="text-slate-400 mt-1">{{ __('Create your account') }}</p>
    </div>

    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-8 shadow-2xl {{ $isRtl ? 'text-right' : '' }}">
        <h2 class="text-xl font-semibold text-white mb-6">{{ __('Sign up') }}</h2>

        @if ($errors->any())
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4 mb-5">
                <ul class="text-red-300 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('Full Name') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="John Doe">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('Email Address') }}</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('Password') }}</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="********">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">{{ __('Confirm Password') }}</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-2.5 bg-white/10 border border-white/20 rounded-lg text-white placeholder-slate-400
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="********">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember', true)) class="w-4 h-4 rounded border-white/30 bg-white/10 text-blue-500 focus:ring-blue-500">
                    <span class="text-sm text-slate-300">{{ __('Remember me') }}</span>
                </label>
            </div>
            <button type="submit"
                class="w-full py-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-lg
                       transition-colors duration-200 shadow-lg shadow-blue-600/30 focus:outline-none focus:ring-2 focus:ring-blue-500">
                {{ __('Create Account') }}
            </button>
        </form>

        <p class="mt-5 text-center text-sm text-slate-300">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="text-blue-300 hover:text-blue-200 font-medium">{{ __('Sign In') }}</a>
        </p>
    </div>

    <p class="text-center text-slate-500 text-sm mt-6">
        &copy; {{ date('Y') }} {{ config('app.display_name', 'WehancePOS') }}. {{ __('All rights reserved.') }}
    </p>
</div>

@livewireScripts
</body>
</html>
