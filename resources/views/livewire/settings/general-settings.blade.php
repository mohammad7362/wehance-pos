<div>
    @if(session('success'))
        <div class="alert-success mb-4">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">{{ __('General Settings') }}</h3>
            <div class="space-y-4">
                <div>
                    <label class="form-label">{{ __('Application Name') }} *</label>
                    <input wire:model="app_name" type="text" class="input-field w-full" />
                    @error('app_name') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('Used across the app shell, login page, and POS receipt.') }}</p>
                </div>
                <div>
                    <label class="form-label">{{ __('Currency') }}</label>
                    <select wire:model="app_currency" class="input-field w-full">
                        <option value="USD">USD - US Dollar</option>
                        <option value="LBP">LBP - Lebanese Lira</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">{{ __('Second Currency') }}</label>
                    <select wire:model="secondary_currency" class="input-field w-full">
                        <option value="">{{ __('None') }}</option>
                        <option value="USD">USD - US Dollar</option>
                        <option value="LBP">LBP - Lebanese Lira</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="GBP">GBP - British Pound</option>
                        <option value="AED">AED - UAE Dirham</option>
                        <option value="SAR">SAR - Saudi Riyal</option>
                    </select>
                    @error('secondary_currency') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('Shown in POS totals alongside the main currency.') }}</p>
                </div>
                @if($secondary_currency)
                <div>
                    <label class="form-label">{{ __('Exchange Rate') }}</label>
                    <input wire:model="exchange_rate" type="number" min="0.0001" step="0.0001" class="input-field w-full" />
                    @error('exchange_rate') <p class="form-error">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('How many units of the second currency equal 1 unit of the main currency.') }}</p>
                </div>
                @endif
                <div>
                    <label class="form-label">{{ __('Default Tax Rate (%)') }}</label>
                    <input wire:model="tax_rate" type="number" min="0" max="100" step="0.1" class="input-field w-full" />
                </div>
                <div>
                    <label class="form-label">{{ __('Receipt Footer Message') }}</label>
                    <textarea wire:model="receipt_footer" class="input-field w-full" rows="3"></textarea>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="font-bold text-lg mb-4">{{ __('Loyalty Program') }}</h3>
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <input wire:model="enable_loyalty" type="checkbox" id="loyalty_toggle" class="w-5 h-5 rounded" />
                    <label for="loyalty_toggle" class="font-medium">{{ __('Enable Loyalty Points') }}</label>
                </div>
                @if($enable_loyalty)
                <div>
                    <label class="form-label">{{ __('Points per Dollar Spent') }}</label>
                    <input wire:model="loyalty_rate" type="number" min="0" step="0.1" class="input-field w-full" />
                    <p class="mt-1 text-xs text-gray-500">{{ __('Applied when a customer is attached to a completed sale.') }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <button wire:click="save" class="btn-primary">{{ __('Save Settings') }}</button>
    </div>
</div>
