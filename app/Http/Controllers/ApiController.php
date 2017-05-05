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

            foreach ($volumes as $currencyKey => $volume) {

                $item = collect();
                $item->put('currency', $currencyKey);
                $item->put('volume', $volume);
                if ($currencyKey == 'BTC') {
                    $rateFiat = $rateBtcFiat;
                    $rateBtc = 1;

                    $yesterdaysRate = $this->trading->getYesterdaysRate(config('api.fiat'), $currencyKey);
                    $sevenDaysAgoRate = $this->trading->getPastRate(7, 7, config('api.fiat'), $currencyKey);

                } else {
                    $rateBtc = $this->trading->getCurrentRate('BTC', $currencyKey);
                    $rateFiat = $rateBtcFiat * $rateBtc;
                    $yesterdaysRate = $this->trading->getYesterdaysRate(config('api.fiat'), $currencyKey);
                    $sevenDaysAgoRate = $this->trading->getPastRate(7, 7, config('api.fiat'), $currencyKey);
                }
                $yesterdaysRateFiat = $yesterdaysRate->get('fiat');
                $yesterdaysRateBtc = $yesterdaysRate->get('btc');
                $sevenDaysAgoRateFiat = $sevenDaysAgoRate->get('fiat');
                $sevenDaysAgoRateBtc = $sevenDaysAgoRate->get('btc');

                if ($yesterdaysRateFiat) {
                    $rateDiffDayFiat = (100 / $yesterdaysRateFiat * $rateFiat) - 100;
                } else {
                    $rateDiffDayFiat = 0;
                }
                if ($yesterdaysRateBtc) {
                    $rateDiffDayBtc = (100 / $yesterdaysRateBtc * $rateBtc) - 100;
                } else {
                    $rateDiffDayBtc = 0;
                }
                if ($sevenDaysAgoRateFiat) {
                    $rateDiffSevenDaysAgoFiat = (100 / $sevenDaysAgoRateFiat * $rateFiat) - 100;
                } else {
                    $rateDiffSevenDaysAgoFiat = 0;
                }
                if ($sevenDaysAgoRateBtc) {
                    $rateDiffSevenDaysAgoBtc = (100 / $sevenDaysAgoRateBtc * $rateBtc) - 100;
                } else {
                    $rateDiffSevenDaysAgoBtc = 0;
                }
                $item->put('rateDiffDayFiat', $rateDiffDayFiat);
                $item->put('rateDiffDayBtc', $rateDiffDayBtc);
                $item->put('rateDiffSevenDaysAgoFiat', $rateDiffSevenDaysAgoFiat);
                $item->put('rateDiffSevenDaysAgoBtc', $rateDiffSevenDaysAgoBtc);
                $item->put('yesterdaysRateFiat', $yesterdaysRateFiat);
                $item->put('yesterdaysRateBtc', $yesterdaysRateBtc);
                $item->put('currentRateBtc', $rateBtc);
                $item->put('currentRateFiat', $rateFiat);
                $item->put('currentValueFiat', $rateFiat * $volume);
                $item->put('currentValueBtc', $rateBtc * $volume);
                if ($currencyKey == 'BTC') {
                    $item->put('averagePurchaseRateBtcCoin', 1);
                } else {
                    $item->put('averagePurchaseRateBtcCoin', $this->trading->getAveragePurchaseRate('BTC', $currencyKey));
                }
                if ($currencyKey == 'BTC') {
                    $item->put('averagePurchaseRateFiatBtc', $this->trading->getAveragePurchaseRateFiatBtc(config('api.fiat'), 'BTC'));
                } else {
                    $item->put('averagePurchaseRateFiatBtc', $this->trading->getAveragePurchaseRateFiatBtc($currencyKey));
                }
                $item->put('averagePurchaseRateCoinFiat', $item['averagePurchaseRateFiatBtc'] * $item['averagePurchaseRateBtcCoin']);
                $item->put('purchaseValueBtc', $volume * $item['averagePurchaseRateBtcCoin']);
                $item->put('purchaseValueFiat', $volume * $item['averagePurchaseRateBtcCoin'] * $item['averagePurchaseRateFiatBtc']);

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
                'tradingRevenueBtc' => $sumBtcFiatTrades['sellVolumeBtc'] - $sumBtcFiatTrades['buyVolumeBtc'],
                'tradingRevenueFiat' => $sumBtcFiatTrades['sellVolumeFiat'] - $sumBtcFiatTrades['buyVolumeFiat'],
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
