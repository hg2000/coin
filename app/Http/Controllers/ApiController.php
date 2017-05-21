<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use \App;

use \App\Utility\Format;
use \App\Service\TradingService;

class ApiController extends BaseController
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
     * Fetches trades from external apis or from cache.
     * Refreshs cache if cachetime is over
     * Returns an json encoded array of all trades.
     * @return string (json)
     */
    public function getTradeHistory()
    {

        try {
            $trades = $this->trading->getTradeHistory();
            return response()->json($trades);

        } catch (\Exception $e) {
            return response()->json([
                $e->getMessage(),
                $e->getTraceAsString()
                ], 500);
        }

    }

    /**
     * Fetches current volume information from external apis
     * and provides all data for the volume view.
     * @return string (json)
     */
    public function getVolumes()
    {
        try {
            $balances = collect();
            $volumes = $this->trading->getCurrentVolumes();
            $rateBtcFiat = $this->trading->getCurrentRate('BTC', config('api.fiat'));

            $balances = $this->trading->getcurrentBalanceInfo();

            $sumBtcFiatTrades = $this->trading->getSumBtcFiatTrades();

            $sum = [
                'buyVolumeBtc' => $sumBtcFiatTrades['buyVolumeBtc'],
                'sellVolumeBtc' => $sumBtcFiatTrades['sellVolumeBtc'],
                'sellVolumeFiat' => $sumBtcFiatTrades['sellVolumeFiat'],
                'buyVolumeFiat' => $sumBtcFiatTrades['buyVolumeFiat'],
                'tradingRevenueBtc' => $sumBtcFiatTrades['sellVolumeBtc'] - $sumBtcFiatTrades['buyVolumeBtc'],
                'tradingRevenueFiat' => $sumBtcFiatTrades['sellVolumeFiat'] - $sumBtcFiatTrades['buyVolumeFiat'],
                'currenValueBtc' => $balances->pluck('currentValueBtc')->sum(),
                'currenValueFiat' => $balances->pluck('currentValueFiat')->sum(),

            ];
            $sum['totalRevenueBtc'] = $sum['tradingRevenueBtc'] + $sum['currenValueBtc'];
            $sum['totalRevenueFiat'] = $sum['tradingRevenueFiat'] + $sum['currenValueFiat'];
            $sum['purchaseValueFiat'] = $balances->pluck('purchaseValueFiat')->sum();
            $sum['currentRevenueFiat'] = $sum['currenValueFiat'] - $sum['purchaseValueFiat'];

            if ($sum['purchaseValueFiat'] > 0) {

                $totalRevenueRate = 100 / $sum['purchaseValueFiat']  * $sum['totalRevenueFiat'];
            } else {
                $totalRevenueRate = 0;
            }

            $sum['totalRevenueRate'] = $totalRevenueRate;

            return response()
            ->json([
                'balances' => $balances,
                'sum' => $sum
            ]);
        } catch (\Exception $e) {
              return response()->json([
                 $e->getMessage(),
                 $e->getTraceAsString()
                 ], 500);
        }
    }

    public function getCoinDetail($key)
    {
        $this->trading->getSellPool($key);
    }
}
