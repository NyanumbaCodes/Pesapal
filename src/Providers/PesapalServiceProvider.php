<?php


namespace NyanumbaCodes\Pesapal\Providers;

use Illuminate\Support\ServiceProvider;

class PesapalServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/pesapal.php', 'pesapal');
        $this->app->singleton('pesapal', function ($app) {
            return new \NyanumbaCodes\Pesapal\Pesapal(config('pesapal'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/pesapal.php' => config_path('pesapal.php'),
        ], 'config');
    }
}
