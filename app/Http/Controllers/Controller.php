<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use \App\Service\CacheService;


class Controller extends BaseController
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getHome()
    {
        $cacheService = \App::make(CacheService::class);
        $lastUpdate = $cacheService->get('lastUpdate');
        return view('home', [
            'fiat' => config('api.fiat'),
            'fiatsymbol' => config('api.fiatSymbol'),
            'lastUpdate' => $lastUpdate
        ]);
    }
}
