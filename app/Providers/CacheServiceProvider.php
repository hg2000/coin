<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Service\CacheService;

class CacheServiceProvider extends ServiceProvider
{
    public function register()
   {
       $this->app->singleton(CacheService::class, function ($app) {
           return new CacheService();
       });
   }
}
