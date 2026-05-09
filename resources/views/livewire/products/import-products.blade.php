<div>
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('products.index') }}" class="btn-secondary inline-flex items-center gap-2">
            <x-pos-icon name="arrow-left" class="w-4 h-4" />
            Back to Products
        </a>
        <a href="{{ route('products.import.template') }}" class="btn-secondary inline-flex items-center gap-2">
            <x-pos-icon name="arrow-down-tray" class="w-4 h-4" />
            Download Template
        </a>
    </div>

    @if($done)
        <div class="alert-success mb-4">
            Import complete — {{ $imported }} product(s) imported.
            @if(count($importErrors))
                {{ count($importErrors) }} row(s) were skipped (see below).
            @endif
        </div>
    @endif

    @if(count($importErrors))
        <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800 space-y-1">
            <p class="font-semibold">Skipped rows:</p>
            @foreach($importErrors as $err)
                <p>{{ $err }}</p>
            @endforeach
        </div>
    @endif

    <div class="card p-6 max-w-3xl">
        <h2 class="text-lg font-bold mb-2">Import Products from Excel</h2>
        <p class="text-sm text-gray-500 mb-5">
            Upload an <code>.xlsx</code>, <code>.xls</code>, or <code>.csv</code> file.
            The first row must be a header row with these columns in order:
            <br>
            <code class="text-xs bg-gray-100 px-1 rounded">name · barcode · sku · category · cost_price · selling_price · tax_rate · min_stock_alert · pieces_per_box · description</code>
            <br>
            Download the template above for a ready-to-fill file.
        </p>

        <div class="space-y-4">
            <div>
                <label class="form-label">Excel / CSV File *</label>
                <input wire:model="file" type="file" accept=".xlsx,.xls,.csv" class="input-field w-full" />
                @error('file') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            @if(count($preview))
                <div class="overflow-x-auto">
                    <p class="text-xs text-gray-500 mb-1">Preview (first 5 data rows):</p>
                    <table class="w-full text-xs border border-gray-200 rounded">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-1 text-left">Name</th>
                                <th class="px-2 py-1 text-left">Barcode</th>
                                <th class="px-2 py-1 text-left">SKU</th>
                                <th class="px-2 py-1 text-left">Category</th>
                                <th class="px-2 py-1 text-right">Cost</th>
                                <th class="px-2 py-1 text-right">Sell</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview as $row)
                            <tr class="border-t border-gray-100">
                                @foreach($row as $cell)
                                <td class="px-2 py-1">{{ $cell }}</td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="flex gap-3 pt-2">
                <button wire:click="import"
                    wire:loading.attr="disabled"
                    @if(!$file) disabled @endif
                    class="btn-primary">
                    <span wire:loading wire:target="import">Importing…</span>
                    <span wire:loading.remove wire:target="import">Import Products</span>
                </button>
            </div>
        </div>
    </div>
</div>
