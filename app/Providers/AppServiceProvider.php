<?php

namespace App\Providers;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appName = 'WehancePOS';

        if (Schema::hasTable('app_settings')) {
            $appName = AppSetting::getValue('app_name', $appName);

            config([
                'app.display_name' => $appName,
                'pos.loyalty.enabled' => AppSetting::getBool('enable_loyalty', false),
                'pos.loyalty.rate' => AppSetting::getFloat('loyalty_rate', 1),
            ]);
        } else {
            config([
                'app.display_name' => $appName,
                'pos.loyalty.enabled' => false,
                'pos.loyalty.rate' => 1.0,
            ]);
        }

        View::share('appDisplayName', config('app.display_name'));
    }
}
