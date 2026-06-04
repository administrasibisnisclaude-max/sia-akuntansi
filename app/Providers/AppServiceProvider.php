<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        View::composer('*', function ($view) {
            $company = Cache::remember('company_settings', 3600, function () {
                return \App\Models\CompanySetting::all()->pluck('value', 'key');
            });
            $view->with('company', $company);
        });

        Blade::directive('active', function ($expression) {
            return "<?php echo (request()->routeIs($expression)) ? 'active' : ''; ?>";
        });

        Blade::directive('idr', function ($expression) {
            return "<?php echo 'Rp ' . number_format($expression, 0, ',', '.'); ?>";
        });
    }
}
