<div>
    {{-- Period Selector --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="flex rounded-lg overflow-hidden border border-gray-200">
            @foreach(['today','yesterday','week','month','year'] as $p)
                <button wire:click="$set('period','{{ $p }}')"
                    class="px-4 py-2 text-sm font-medium {{ $period === $p ? 'bg-primary text-white' : 'hover:bg-gray-50 text-gray-600' }}">
                    {{ __(ucfirst($p)) }}
                </button>
            @endforeach
        </div>
        <select wire:model.live="branch_id" class="input-field">
            <option value="">{{ __('All Branches') }}</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <div class="card p-4 col-span-2">
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Total Revenue') }}</p>
            <p class="text-2xl font-bold text-primary mt-1">${{ number_format($totalRevenue, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Total Sales') }}</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($totalSales) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Avg Order') }}</p>
            <p class="text-2xl font-bold mt-1">${{ number_format($avgOrderValue, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Expenses') }}</p>
            <p class="text-2xl font-bold text-red-500 mt-1">${{ number_format($totalExpenses, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ __('Net Profit') }}</p>
            <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                ${{ number_format($netProfit, 2) }}
            </p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card p-6 mb-6">
        <h3 class="font-semibold mb-4">{{ __('30-Day Revenue Trend') }}</h3>
        <div class="h-48 flex items-end gap-1">
            @php $maxVal = max(array_merge($revenueArr, [1])); @endphp
            @foreach($revenueArr as $i => $val)
                <div class="flex-1 flex flex-col items-center gap-1 group relative">
                    <div class="w-full bg-primary/80 rounded-t hover:bg-primary transition-all"
                        @style(['height: ' . max(2, round(($val / $maxVal) * 160)) . 'px'])
                        title="{{ $labels[$i] }}: ${{ number_format($val, 2) }}">
                    </div>
                </div>
            @endforeach
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>{{ $labels[0] ?? '' }}</span>
            <span>{{ $labels[14] ?? '' }}</span>
            <span>{{ $labels[29] ?? '' }}</span>
        </div>
    </div>

    {{-- Links --}}
    <div class="grid grid-cols-3 gap-4">
        <a href="{{ route('reports.sales') }}" class="card p-4 hover:shadow-md transition flex items-center gap-3">
            <x-pos-icon name="chart-bar" class="w-8 h-8 text-blue-500" />
            <div><p class="font-semibold">{{ __('Sales Report') }}</p><p class="text-xs text-gray-500">{{ __('Detailed transactions') }}</p></div>
        </a>
        <a href="{{ route('reports.inventory') }}" class="card p-4 hover:shadow-md transition flex items-center gap-3">
            <x-pos-icon name="archive-box" class="w-8 h-8 text-green-500" />
            <div><p class="font-semibold">{{ __('Inventory Report') }}</p><p class="text-xs text-gray-500">{{ __('Stock levels & value') }}</p></div>
        </a>
        <a href="{{ route('reports.profit_loss') }}" class="card p-4 hover:shadow-md transition flex items-center gap-3">
            <x-pos-icon name="currency-dollar" class="w-8 h-8 text-purple-500" />
            <div><p class="font-semibold">{{ __('Profit & Loss') }}</p><p class="text-xs text-gray-500">{{ __('Revenue vs expenses') }}</p></div>
        </a>
    </div>
</div>
