<div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search product..." class="input-field w-56" />
        <select wire:model.live="branch_id" class="input-field">
            <option value="">All Branches</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="type" class="input-field">
            <option value="">All Types</option>
            @foreach($types as $t)
                <option value="{{ $t }}">{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateFrom" type="date" class="input-field" />
        <input wire:model.live="dateTo" type="date" class="input-field" />
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="table-header">
                <tr>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Product</th>
                    <th class="px-4 py-3 text-left">Branch</th>
                    <th class="px-4 py-3 text-center">Type</th>
                    <th class="px-4 py-3 text-center">Qty Change</th>
                    <th class="px-4 py-3 text-center">Before</th>
                    <th class="px-4 py-3 text-center">After</th>
                    <th class="px-4 py-3 text-left">Note</th>
                    <th class="px-4 py-3 text-left">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($movements as $mov)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $mov->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $mov->product?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $mov->branch?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge-{{ $mov->type === 'sale' ? 'red' : ($mov->type === 'purchase' ? 'green' : 'blue') }}">
                                {{ ucfirst($mov->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold {{ $mov->quantity >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $mov->quantity >= 0 ? '+' : '' }}{{ $mov->quantity }}
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $mov->quantity_before }}</td>
                        <td class="px-4 py-3 text-center text-gray-700">{{ $mov->quantity_after }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $mov->notes ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $mov->user?->name ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-gray-400">No movements found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t">{{ $movements->links() }}</div>
    </div>
</div>
