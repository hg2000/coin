<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use \App;

use \App\Utility\Format;
use \App\Service\TradingService;
use \App\Service\RateService;

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
    public function getTradeHistory($from = null, $to = null, $currencyKey = null)
    {
        try {
            $trades = $this->trading->getTradeHistory();
            return response()->json($trades);

        } catch (\Exception $e) {
            return $this->returnException($e);
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
                'currentValueBtc' => $balances->pluck('currentValueBtc')->sum(),
                'currentValueFiat' => $balances->pluck('currentValueFiat')->sum(),

            ];

            $sum['purchaseValueFiat'] = $balances->pluck('purchaseValueFiat')->sum();
            $sum['purchaseValueBtc'] = $balances->pluck('purchaseValueBtc')->sum();
            $sum['currentRevenueBtc'] = $sum['currentValueBtc'] - $sum['purchaseValueBtc'];
            $sum['currentRevenueFiat'] = $sum['currentValueFiat'] - $sum['purchaseValueFiat'];
            $sum['totalRevenueFiat'] = $sum['tradingRevenueFiat'] + $sum['currentRevenueFiat'];

            $sum['totalRevenueFiat'] = $sum['tradingRevenueFiat'] + $sum['currentValueFiat'];
            $sum['totalRevenueBtc'] = $sum['tradingRevenueBtc'] + $sum['currentValueBtc'];

            if ($sum['purchaseValueFiat'] > 0) {
                $tradingRevenueRateFiat = 100 / $sum['purchaseValueFiat'] * $sum['currentRevenueFiat'];
            } else {
                $tradingRevenueRateFiat = 0;
            }
            $sum['tradingRevenueRateFiat'] = $tradingRevenueRateFiat;

            if ($sum['purchaseValueBtc'] > 0) {
                $tradingRevenueRateBtc = 100 / $sum['purchaseValueBtc'] * $sum['currentRevenueBtc'];
            } else {
                $tradingRevenueRateBtc = 0;
            }
            $sum['tradingRevenueRateBtc'] = $tradingRevenueRateBtc;

            return response()
            ->json([
                'balances' => $balances,
                'sum' => $sum
            ]);
        } catch (\Exception $e) {
            return $this->returnException($e);
        }
    }

    public function getCoinDetail($key)
    {
        try {

            $sellPool = $this->trading->getSellPool($key, 'BTC')->sortByDesc('date');

            // Necessery because otherwise ordering falls bas to original order while iterating in the template
            foreach($sellPool as $item) {
                $result[] = $item;
            }
            return response()->json(
                [
                    'sellPool' => $result
                ]
            );
        } catch (\Exception $e) {
            return $this->returnException($e);
        }

    }

    protected function returnException(\Exception $e)
    {
        return response()->json([
           $e->getMessage(),
           $e->getTraceAsString()
           ], 500);
    }
}
