<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ApiDriverProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $apis = explode(',', config('api.active'));

        foreach ($apis as $apiKey) {
            $conf = config('api.drivers.' . $apiKey);
            if ($conf) {
                $this->app->singleton($conf['driverClass'], function ($app) use ($conf) {
                    return new $conf['driverClass']($conf['key'], $conf['secret']);
                });
            }
        }
    }
}
