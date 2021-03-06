<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Service\CacheService;

use \App;

use \App\Utility\Format;
use \App\Service\TradingService;
use \App\Service\RateService;


class ApiController extends BaseController
{
    /**
     * @var \App\Service\TradingService
     */
    protected $tradeService;

    /**
     * @var \App\Service\RateService
     */
    protected $rateService;

    public function __construct()
    {
        $this->tradeService = App::make(TradingService::class);
        $this->rateService = App::make(RateService::class);
    }

    /**
     * Fetches trades from external apis or from cache.
     * Refreshs cache if cachetime is over
     * Returns an json encoded array of all trades.
     * @return string (json)
     */
    public function getTradeHistory($from = null, $to = null, $currencyKey = null)
    {
        $trades = $this->tradeService->getTradeHistory();
        return response()->json($trades);
    }


    /**
     * Fetches current volume information from external apis
     * and provides all data for the volume view.
     * @return string (json)
     */
    public function getPortfolio()
    {

        $volumes = $this->tradeService->getCurrentVolumes();
        $rateBtcFiat = $this->tradeService->getCurrentRate('BTC', config('api.fiat'));
        $balances = collect();
        $balances = $this->tradeService->getcurrentBalanceInfo();

        $sumBtcFiatTrades = $this->tradeService->getSumBtcFiatTrades();

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

        $today = new \DateTime();
        $today->setTime(23,59);
        $lastWeek = new \DateTime();
        $lastWeek->sub(new \DateInterval('P14D'));
        $lastWeek->setTime(0,0);
        $dailyRateAverage = $this->rateService->getDailyRateAverage($lastWeek, $today);

        return response()
        ->json([
            'balances' => $balances,
            'sum' => $sum,
            'dailyRateAverage' => $dailyRateAverage

        ]);
    }

    /**
     * Deletes the Cache and fetches data from the apis
     */
    public function getRefresh()
    {
        $cacheService = resolve(CacheService::class);
        $cacheService->clear();
        $this->getPortfolio();
        $this->getTradeHistory();
        date_default_timezone_set(config('format.timezone'));
        $now = new \DateTime();
        $cacheService->set('lastUpdate', $now->format(config('format.datetime')));
        return response()
        ->json([
            'note' => trans('global.refresh')
        ]);
    }

    public function getLastRefreshDateTime() {
        $cacheService = resolve(CacheService::class);
        $lastRefresh = $cacheService->get('lastUpdate');
        return response()
        ->json([
             $lastRefresh
        ]);
    }
}
