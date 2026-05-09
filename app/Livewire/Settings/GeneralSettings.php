<?php

namespace App\Livewire\Settings;

use App\Models\Branch;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public string $app_name     = '';
    public string $app_currency = 'USD';
    public string $secondary_currency = '';
    public string $exchange_rate = '1';
    public string $tax_rate     = '0';
    public string $receipt_footer = '';
    public bool   $enable_loyalty = false;
    public string $loyalty_rate   = '1'; // points per dollar
    public ?int   $branchId = null;
    public $receipt_logo = null;
    public ?string $currentReceiptLogo = null;

    public function mount(): void
    {
        $branch = Auth::user()?->branch;

        $this->branchId       = $branch?->id;
        $this->app_name       = AppSetting::getValue('app_name', 'WehancePOS');
        $this->app_currency   = $branch?->currency ?? 'USD';
        $this->secondary_currency = $branch?->secondary_currency ?? '';
        $this->exchange_rate  = (string) ($branch?->exchange_rate ?? '1');
        $this->tax_rate       = (string) ($branch?->tax_rate ?? '0');
        $this->receipt_footer = $branch?->receipt_footer ?? 'Thank you for your business!';
        $this->enable_loyalty = AppSetting::getBool('enable_loyalty', false);
        $this->loyalty_rate   = (string) AppSetting::getFloat('loyalty_rate', 1);
        $this->currentReceiptLogo = AppSetting::getValue('receipt_logo');
    }

    public function save(): void
    {
        $this->validate([
            'app_name'       => 'required|string|max:100',
            'app_currency'   => 'required|string|max:10',
            'secondary_currency' => 'nullable|string|max:10|different:app_currency',
            'exchange_rate'  => 'nullable|required_with:secondary_currency|numeric|min:0.0001',
            'tax_rate'       => 'required|numeric|min:0|max:100',
            'receipt_footer' => 'nullable|string|max:500',
            'loyalty_rate'   => 'required|numeric|min:0',
            'receipt_logo'   => 'nullable|image|max:2048',
        ]);

        if ($this->branchId) {
            $currency = strtoupper(trim($this->app_currency));
            $secondaryCurrency = strtoupper(trim($this->secondary_currency));
            $secondaryCurrency = $secondaryCurrency === '' ? null : $secondaryCurrency;

            Branch::findOrFail($this->branchId)->update([
                'currency' => $currency,
                'currency_symbol' => $this->currencySymbol($currency),
                'secondary_currency' => $secondaryCurrency,
                'secondary_currency_symbol' => $secondaryCurrency ? $this->currencySymbol($secondaryCurrency) : null,
                'exchange_rate' => $secondaryCurrency ? (float) $this->exchange_rate : null,
                'tax_rate' => $this->tax_rate,
                'receipt_footer' => $this->receipt_footer ?: null,
            ]);

            AppSetting::setValue('app_name', trim($this->app_name));
            AppSetting::setValue('enable_loyalty', $this->enable_loyalty);
            AppSetting::setValue('loyalty_rate', $this->loyalty_rate);

            if ($this->receipt_logo) {
                $path = $this->receipt_logo->store('logos', 'public');
                AppSetting::setValue('receipt_logo', $path);
                $this->currentReceiptLogo = $path;
                $this->receipt_logo = null;
            }

            config([
                'app.display_name' => trim($this->app_name),
                'pos.loyalty.enabled' => $this->enable_loyalty,
                'pos.loyalty.rate' => (float) $this->loyalty_rate,
            ]);

            session()->flash('success', __('Settings saved successfully.'));
            return;
        }

        session()->flash('error', __('No active branch is assigned to your account, so branch settings could not be saved.'));
    }

    public function render()
    {
        return view('livewire.settings.general-settings')
            ->layout('layouts.app', ['title' => 'General Settings']);
    }

    private function currencySymbol(string $currency): string
    {
        return match ($currency) {
            'USD' => '$',
            'EUR' => 'EUR',
            'GBP' => 'GBP',
            'AED' => 'AED',
            'SAR' => 'SAR',
            'LBP' => 'LBP',
            default => $currency,
        };
    }
}
