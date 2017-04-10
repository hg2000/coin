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

    public function getVolumes()
    {
        try {
            $balances = collect();
            $volumes = $this->trading->getVolumes();
            $rateBtcFiat = $this->trading->getCurrentRate('BTC', config('api.fiat'));

            foreach ($volumes as $currencyKey => $volume) {

                $item = collect();
                $item->put('currency', $currencyKey);
                $item->put('volume', $volume);
                if ($currencyKey == 'BTC') {
                    $rateEur = $rateBtcFiat;
                    $rateBtc = 1;
                } else {
                    $rateBtc = $this->trading->getCurrentRate('BTC', $currencyKey);
                    $rateEur = $rateBtcFiat * $rateBtc;
                }
                $item->put('currentRateBtc', $rateBtc);
                $item->put('currentRateFiat', $rateEur);
                $item->put('currentValueFiat', $rateEur * $volume);
                $item->put('currentValueBtc', $rateBtc * $volume);
                $item->put('averageBuyRateBtcCoin', $this->trading->getAverageBuyRate('BTC', $currencyKey));
                $item->put('averageSellRateBtcCoin', $this->trading->getAverageSellRate($currencyKey, 'BTC'));
                $item->put('averagePurchaseRateBtcFiat', $this->trading->getAvgPurchaseRate($currencyKey));
                $item->put('averagePurchaseRateCoinFiat', $item['averagePurchaseRateBtcFiat'] * $item['averageBuyRateBtcCoin']);
                $item->put('purchaseValueBtc', $volume * $item['averageBuyRateBtcCoin']);
                $item->put('purchaseValueFiat', $volume * $item['averageBuyRateBtcCoin'] * $item['averagePurchaseRateBtcFiat']);

                if ($currencyKey == 'BTC') {
                    $item->put('sellVolume', $this->trading->getSellVolume('BTC', config('api.fiat')));
                } else {
                    $item->put('sellVolume', $this->trading->getSellVolume($currencyKey, 'BTC'));
                }
                $item->put('revenueFiat', $item['currentValueFiat'] - $item['purchaseValueFiat']);
                $item->put('revenueBTC', $item['currentValueBtc'] - $item['purchaseValueBtc']);

                $balances->push($item);
            }
            $sumBtcFiatTrades = $this->trading->getSumBtcFiatTrades();


            $sum = [
                'buyVolumeBtc' => $sumBtcFiatTrades['buyVolumeBtc'],
                'sellVolumeBtc' => $sumBtcFiatTrades['sellVolumeBtc'],
                'sellVolumeFiat' => $sumBtcFiatTrades['sellVolumeFiat'],
                'buyVolumeFiat' => $sumBtcFiatTrades['buyVolumeFiat'],
                'tradingRevenueBtc' => $sumBtcFiatTrades['buyVolumeBtc'] - $sumBtcFiatTrades['sellVolumeBtc'],
                'tradingRevenueFiat' => $sumBtcFiatTrades['buyVolumeFiat'] - $sumBtcFiatTrades['sellVolumeFiat'],
                'currenValueBtc' => $balances->pluck('currentValueBtc')->sum(),
                'currenValueFiat' => $balances->pluck('currentValueFiat')->sum(),

            ];
            $sum['totalRevenueBtc'] = $sum['tradingRevenueBtc'] + $sum['currenValueBtc'];
            $sum['totalRevenueFiat'] = $sum['tradingRevenueFiat'] + $sum['currenValueFiat'];
            $sum['purchaseValueFiat'] = $balances->pluck('purchaseValueFiat')->sum();
            $sum['currentRevenueFiat'] = $sum['currenValueFiat'] - $sum['purchaseValueFiat'];

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
}
