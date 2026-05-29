<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Blade::directive('active', function ($expression) {
            return "<?php echo (request()->routeIs($expression)) ? 'active' : ''; ?>";
        });

        Blade::directive('idr', function ($expression) {
            return "<?php echo 'Rp ' . number_format($expression, 0, ',', '.'); ?>";
        });
    }
}
