<div>
    <div class="flex items-center justify-between mb-4">
        <div class="flex flex-wrap items-center gap-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('PO # or supplier...') }}" class="input-field w-56" />
            <select wire:model.live="supplier_id" class="input-field">
                <option value="">{{ __('All Suppliers') }}</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="input-field">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="draft">{{ __('Draft') }}</option>
                <option value="sent">{{ __('Sent') }}</option>
                <option value="partial">{{ __('Partial') }}</option>
                <option value="received">{{ __('Received') }}</option>
                <option value="cancelled">{{ __('Cancelled') }}</option>
            </select>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn-primary flex items-center gap-2">
            <x-pos-icon name="plus" class="w-4 h-4" /> {{ __('New PO') }}
        </a>
    </div>

    @if(session('success'))
        <div class="alert-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">{{ __('PO #') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Date') }}</th>
                    <th class="px-4 py-3 text-left">{{ __('Supplier') }}</th>
                    <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Total') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono font-semibold text-primary">{{ $po->reference_no }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ($po->ordered_at ?? $po->created_at)->locale(app()->getLocale())->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $po->supplier?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge-{{ $po->status === 'received' ? 'green' : ($po->status === 'sent' ? 'blue' : ($po->status === 'cancelled' ? 'red' : 'amber')) }}">
                                {{ __(ucfirst($po->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">${{ number_format($po->total_amount, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('purchases.show', $po->id) }}" class="btn-icon-edit">
                                    <x-pos-icon name="eye" class="w-4 h-4" />
                                </a>
                                @if(in_array($po->status, ['draft','sent']))
                                    <a href="{{ route('purchases.edit', $po->id) }}" class="btn-icon-edit">
                                        <x-pos-icon name="pencil" class="w-4 h-4" />
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">{{ __('No purchase orders found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $orders->links() }}</div>
    </div>
</div>
