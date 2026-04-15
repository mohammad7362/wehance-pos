<div>
    <div class="mb-4">
        <a href="{{ route('sales.index') }}" class="btn-secondary inline-flex items-center gap-2">
            <x-pos-icon name="arrow-left" class="w-4 h-4" />
            Back to Sales
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Sale Info --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">Invoice #{{ $sale->reference_no }}</h2>
                    <span class="badge-{{ $sale->status === 'completed' ? 'green' : ($sale->status === 'cancelled' ? 'red' : 'amber') }} text-sm px-3 py-1">
                        {{ ucfirst($sale->status) }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Date:</span>
                        <span class="ml-2 font-medium">{{ $sale->created_at->format('M d, Y H:i') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Cashier:</span>
                        <span class="ml-2 font-medium">{{ $sale->cashier?->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Branch:</span>
                        <span class="ml-2 font-medium">{{ $sale->branch?->name ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Customer:</span>
                        <span class="ml-2 font-medium">{{ $sale->customer?->name ?? 'Walk-in' }}</span>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="card overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-4 py-3 text-left">Product</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-right">Unit Price</th>
                            <th class="px-4 py-3 text-right">Discount</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $item->product?->name ?? '—' }}</div>
                                    @if($item->variant) <div class="text-xs text-gray-400">{{ $item->variant->name }}</div> @endif
                                </td>
                                <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-right text-red-500">
                                    {{ $item->discount_amount > 0 ? '-$' . number_format($item->discount_amount, 2) : '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Totals --}}
        <div class="space-y-4">
            <div class="card p-6">
                <h3 class="font-semibold mb-4">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span>${{ number_format($sale->subtotal, 2) }}</span>
                    </div>
                    @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-red-500">
                        <span>Discount</span>
                        <span>-${{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    @if($sale->tax_amount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Tax</span>
                        <span>${{ number_format($sale->tax_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-lg border-t pt-2 mt-2">
                        <span>Total</span>
                        <span>${{ number_format($sale->total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payments --}}
            <div class="card p-6">
                <h3 class="font-semibold mb-4">Payments</h3>
                @forelse($sale->payments as $payment)
                    <div class="flex justify-between text-sm py-1">
                        <span class="text-gray-600">{{ ucfirst($payment->method) }}</span>
                        <span class="font-medium">${{ number_format($payment->amount, 2) }}</span>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">No payment records.</p>
                @endforelse
            </div>

            <button onclick="window.print()" class="btn-secondary w-full flex items-center justify-center gap-2">
                <x-pos-icon name="printer" class="w-4 h-4" />
                Print Receipt
            </button>
        </div>
    </div>
</div>
