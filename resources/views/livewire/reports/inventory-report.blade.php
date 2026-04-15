<div>
    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total SKUs</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($stats['total_items']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Low Stock</p>
            <p class="text-2xl font-bold text-amber-500 mt-1">{{ number_format($stats['low_stock']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Out of Stock</p>
            <p class="text-2xl font-bold text-red-500 mt-1">{{ number_format($stats['out_of_stock']) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Value</p>
            <p class="text-2xl font-bold text-green-600 mt-1">${{ number_format($stats['total_value'], 2) }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search..." class="input-field w-56" />
        <select wire:model.live="branch_id" class="input-field">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="category" class="input-field">
            <option value="">All Categories</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="stock" class="input-field">
            <option value="">All Stock</option>
            <option value="ok">In Stock</option>
            <option value="low">Low Stock</option>
            <option value="out">Out of Stock</option>
        </select>
        <button wire:click="exportExcel" wire:loading.attr="disabled" class="btn-primary">
            Export Excel
        </button>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">Product</th>
                    <th class="px-4 py-3 text-left">Category</th>
                    <th class="px-4 py-3 text-left">Branch</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3 text-center">Reorder</th>
                    <th class="px-4 py-3 text-right">Cost Value</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($inventory as $inv)
                    @php
                        $reorderLevel = $inv->product?->min_stock_alert ?? 0;
                        $st = $inv->quantity <= 0 ? 'out' : ($inv->quantity <= $reorderLevel ? 'low' : 'ok');
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $inv->product->name }}</div>
                            <div class="text-xs text-gray-400">{{ $inv->product->sku }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $inv->product->category?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $inv->branch?->name }}</td>
                        <td class="px-4 py-3 text-center font-semibold {{ $st === 'out' ? 'text-red-600' : ($st === 'low' ? 'text-amber-600' : 'text-green-600') }}">
                            {{ number_format($inv->quantity, 2) }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $reorderLevel }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($inv->quantity * ($inv->product->cost_price ?? 0), 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($st === 'out') <span class="badge-red">Out</span>
                            @elseif($st === 'low') <span class="badge-amber">Low</span>
                            @else <span class="badge-green">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">No inventory records.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $inventory->links() }}</div>
    </div>
</div>
