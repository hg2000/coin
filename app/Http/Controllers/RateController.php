<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use \App;
use \App\Rate;
use \App\Service\TradingService;
use \Carbon\Carbon;

class RateController extends BaseController
{
    /**
     * @var \App\Service\TradingService
     */
    protected $trading;

    public function __construct()
    {
        $this->trading = App::make(TradingService::class);
    }


    /**
     * Requests all current rates from the api drivers
     * and stores them in the db
     */
    public function storeCurrentRates()
    {
        try {
            $balances = collect();
            $volumes = $this->trading->getCurrentVolumes();
            $rateBtcFiat = $this->trading->getCurrentRate('BTC', config('api.fiat'));
            $rates = collect();
            foreach ($volumes as $currencyKey => $volume) {
                if ($currencyKey == 'BTC') {
                    $rateBtc = 1;
                    $rateFiat = $rateBtcFiat;

                } else {
                    $rateBtc = $this->trading->getCurrentRate('BTC', $currencyKey);
                    $rateFiat = $rateBtc * $rateBtcFiat;

                }
                $rates[$currencyKey] = [$rateBtc, $rateFiat];
            }

            $rate = App::make(Rate::class);
            $rate->updated_at = Carbon::now();
            $rate->created_at = Carbon::now();
            $rate->date = Carbon::now();
            $rate->rates = serialize($rates->toArray());
            $rate->save();
            return response('rates stored');

        } catch (\Exception $e) {
             return response()->json([
                $e->getMessage(),
                $e->getTraceAsString()
                ], 500);

        }

    }
}
