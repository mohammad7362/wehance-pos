@php
    $isActive = request()->routeIs($route . '*');
@endphp
<a href="{{ route($route) }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ $isActive
       ? 'bg-blue-600 text-white shadow-md shadow-blue-900/30'
       : 'text-slate-400 hover:text-white hover:bg-slate-700/60' }}">
    <x-pos-icon :name="$icon" class="w-5 h-5 flex-shrink-0" />
    <span x-show="sidebarOpen" x-transition class="font-medium truncate">{{ __($label) }}</span>
</a>
