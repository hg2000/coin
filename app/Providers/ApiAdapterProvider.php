<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ApiAdapterProvider extends ServiceProvider
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
            $conf = config('api.adapters.' . $apiKey);
            if ($conf) {
                $this->app->singleton($conf['adapterClass'], function ($app) use ($conf) {
                    return new $conf['adapterClass']($conf['key'], $conf['secret']);
                });
            }
        }
    }
}
