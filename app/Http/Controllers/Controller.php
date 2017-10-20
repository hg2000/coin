<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use \App\Service\CacheService;


class Controller extends BaseController
{

    public function __construct() {
        $this->middleware('auth');
    }
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getMain()
    {
        return view('main', [
            'fiat' => config('api.fiat'),
            'fiatsymbol' => config('api.fiatSymbol'),
        ]);
    }

}
