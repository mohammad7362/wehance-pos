<div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Invoice # or customer...') }}" class="input-field w-56" />
        <select wire:model.live="branch_id" class="input-field">
            <option value="">{{ __('All Branches') }}</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="status" class="input-field">
            <option value="">{{ __('All Statuses') }}</option>
            <option value="completed">{{ __('Completed') }}</option>
            <option value="cancelled">{{ __('Cancelled') }}</option>
            <option value="refunded">{{ __('Refunded') }}</option>
        </select>
        <input wire:model.live="dateFrom" type="date" class="input-field" />
        <input wire:model.live="dateTo" type="date" class="input-field" />
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('Invoice') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Customer') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Branch') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Total') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-semibold text-primary">{{ $sale->reference_no }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $sale->created_at->locale(app()->getLocale())->translatedFormat('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $sale->customer?->name ?? __('Walk-in') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $sale->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge-{{ $sale->status === 'completed' ? 'green' : ($sale->status === 'cancelled' ? 'red' : 'amber') }}">
                                {{ __(ucfirst($sale->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">${{ number_format($sale->total, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn-icon-edit">
                                    <x-pos-icon name="eye" class="w-4 h-4" />
                                </a>
                                @if($sale->status === 'completed')
                                    <button wire:click="voidSale({{ $sale->id }})"
                                        wire:confirm="{{ __('Are you sure you want to void this sale?') }}"
                                        class="btn-icon-delete" title="{{ __('Void Sale') }}">
                                        <x-pos-icon name="x-mark" class="w-4 h-4" />
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">{{ __('No sales found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $sales->links() }}</div>
    </div>
</div>
