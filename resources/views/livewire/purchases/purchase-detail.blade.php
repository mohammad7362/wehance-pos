<div>
    <div class="mb-4">
        <a href="{{ route('purchases.index') }}" class="btn-secondary inline-flex items-center gap-2">
            <x-pos-icon name="arrow-left" class="w-4 h-4" /> Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-4">
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold">PO #{{ $po->reference_no }}</h2>
                    <span class="badge-{{ $po->status === 'received' ? 'green' : ($po->status === 'sent' ? 'blue' : 'amber') }}">
                        {{ ucfirst($po->status) }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-gray-500">Supplier:</span> <span class="ml-2 font-medium">{{ $po->supplier?->name }}</span></div>
                    <div><span class="text-gray-500">Branch:</span> <span class="ml-2 font-medium">{{ $po->branch?->name }}</span></div>
                    <div><span class="text-gray-500">Order Date:</span> <span class="ml-2">{{ $po->ordered_at?->format('M d, Y') }}</span></div>
                    <div><span class="text-gray-500">Expected:</span> <span class="ml-2">{{ $po->expected_at?->format('M d, Y') ?? '—' }}</span></div>
                    @if($po->notes)
                    <div class="col-span-2"><span class="text-gray-500">Notes:</span> <span class="ml-2">{{ $po->notes }}</span></div>
                    @endif
                </div>
            </div>

            <div class="card overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-4 py-3 text-left">Product</th>
                            <th class="px-4 py-3 text-center">Qty</th>
                            <th class="px-4 py-3 text-right">Unit Cost</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($po->items as $item)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $item->product?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($item->total_cost, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-6 h-fit">
            <h3 class="font-semibold mb-4">PO Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Discount</span><span>${{ number_format($po->discount_amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Tax</span><span>${{ number_format($po->tax_amount, 2) }}</span></div>
                <div class="flex justify-between font-bold text-lg border-t pt-2"><span>Total</span><span>${{ number_format($po->total_amount, 2) }}</span></div>
            </div>
        </div>
    </div>
</div>
