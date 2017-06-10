<?php
namespace App\Service;

use \App;
use \App\Trade;
use \App\Rate;
use \App\TradePool;
use \App\Service\Helper;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TradingService
{

    protected $resultCache;

    /**
     * Returns the history of all trades
     * TODO Implement time filter!
     *
     * @param  DateTime $from
     * @param  DateTime $to
     * @return Collection
     */
    public function getTradeHistory($from = null, $to = null, $currencyKey = null)
    {

        if (!$from) {
            $from = Carbon::create(1970);
        }
        if (!$to) {
            $to = Carbon::now();
        }
        $lastUpdate = Trade::orderBy('updated_at', 'asc')->take(1)->get()->first();

        if (empty($lastUpdate)) {
            $this->updateTradeHistory(true);
        } else {
            $lastUpdate = $lastUpdate->updated_at;
            $diff = $lastUpdate->diffInMinutes(Carbon::now());
            if ($diff >= config('api.tradeHistory.cacheDuration')) {
                if (config('api.tradeHistory.enableUpdate')) {
                    $this->updateTradeHistory();
                }
            }
        }

        if ($currencyKey) {
            $trades = Trade::where('source_currency', 'LIKE', $currencyKey)
                            ->orWhere('target_currency', 'LIKE', $currencyKey)
                            ->orderBy('date', 'desc')
                            ->get();
        } else {
            $trades = Trade::orderBy('date', 'desc')
            ->get();
        }

        $trades = $trades->map(function ($item) {
            return $item->addAll();
        });
        return $trades;
    }


    protected function getTradeCorrections()
    {

        $corrections = config('api.dateCorrections');
        if (!$corrections) {
            return null;
        }

        $rows = explode(';', $corrections);
        $rows = array_map(function ($item) {
            $row = explode(',', $item);

            $result['platform_id'] = $row[0];
            $result['trade_id'] = $row[1];
            $result['date'] = $row[2];

            return $result;

        }, $rows);

        return collect($rows);
    }

    /**
     * Trades are recieved with a wrong date they can be corrected via .env
     * Its important that trades have the correct order (by date) orherwise the calculation
     * of avergae_purchase_values will become adventurous.
     *
     * @param  Trade  $trade [description]
     * @return [type]        [description]
     */
    protected function applyTradeCorrections(Trade $trade)
    {
        $corrections = $this->getTradeCorrections();

        if (!$corrections) {
            return $trade;
        }

        $filtered = $corrections
                        ->where('platform_id', $trade->platform_id)
                        ->where('trade_id', $trade->trade_id)
                        ->first();

        if ($filtered) {
            $trade->date = $filtered['date'];
        }
        return $trade;
    }

    /**
     * Stores the trade history in the database
     * param boolean  forces the refresh of the whole trade history
     */
    public function updateTradeHistory($forceRefresh = null)
    {
        if ($forceRefresh === null) {
            $forceRefresh = config('api.tradeHistory.forceCompleteRefresh');
        }
        $startTime = Carbon::now();

        if ($forceRefresh) {
            $from = carbon::create(1970);
        } else {
            $lastTrade = Trade::orderBy('date', 'desc')->take(1)->get()->first();
            if ($lastTrade) {
                $from = Carbon::parse($lastTrade->date);
            } else {
                $from = Carbon::create(1970);
            }
        }

        $adapters = $this->getActiveAdapters();

        foreach ($adapters as $adapter) {
            $trades = $adapter->getTradeHistory($from, Carbon::now());
            foreach ($trades as $trade) {
                $trade->created_at = Carbon::now();
                $trade->updated_at = Carbon::now();
                $trade->volume = $trade->volume - $trade->fee_coin;

                if ($check = Trade::where('trade_id', '=', $trade->trade_id)
                            ->where('platform_id', '=', $trade->platform_id)
                            ->take(1)
                            ->get()
                            ->isEmpty()
                        ) {


                    $trade = $this->applyTradeCorrections($trade);

                    $tradeAttributes = $trade->toArray();
                    unset($tradeAttributes['original_volume']);
                    Trade::insert($tradeAttributes);
                }
            }
        }
        $this->updatePurchaseRatesFiatBtc();
        $executionTime = $startTime->diffInSeconds(Carbon::now());
        Log::info('Updating trade history took ' . $executionTime . ' seconds.');
    }

    /**
     * Calculates the Fiat/BTC rate on date of trade for all trades
     */
    public function updatePurchaseRatesFiatBtc()
    {
        $trades = Trade::orderBy('date', 'asc')
                       ->get();

        $btcVolume = 0;
        $avgRate = 0;

        foreach ($trades as $trade) {
            if ($trade->source_currency == config('api.fiat') && $trade->target_currency == 'BTC' && $trade->type == 'buy') {

                $avgRate = ($btcVolume * $avgRate + $trade->volume * $trade->rate) / ($btcVolume + $trade->volume);
                $btcVolume += $trade->volume;


            } elseif ($trade->source_currency == 'BTC' && $trade->target_currency == config('api.fiat') && $trade->type == 'sell') {
                $btcVolume -= $trade->volume;
                if ($btcVolume < 0) {
                    $btcVolume = 0 ;
                }
            }


            $trade->purchase_rate_fiat_btc = $avgRate;
            Trade::where('id', $trade->id)
                    ->update(['purchase_rate_fiat_btc' => $avgRate]);
        }
        return $this;
    }

    protected function calculateAverageRate($currencyKeyA, $currencyKeyB, $field = 'rate', $date = null)
    {

        if (!$date) {
            $date = Carbon::now();
        }

        $trades = Trade::orderBy('date', 'asc')
                        ->where('date', '<=', $date)
                        ->get();

        $btcVolume = 0;
        $avgRate = 0;

        foreach ($trades as $trade) {

            if ($trade->source_currency == $currencyKeyA && $trade->target_currency == $currencyKeyB && $trade->type == 'buy') {

                $avgRate = ($btcVolume * $avgRate + $trade->volume * $trade->$field) / ($btcVolume + $trade->volume);
                $btcVolume += $trade->volume;

            } elseif ($trade->source_currency == $currencyKeyB && $trade->target_currency == $currencyKeyA && $trade->type == 'sell') {
                $btcVolume -= $trade->volume;
                if ($btcVolume < 0) {
                    $btcVolume = 0 ;
                }
            }
        }
        return $avgRate;

    }

    public function getAveragePurchaseRateBtcCoin($currencyKeyA, $currencyKeyB, $date = null)
    {

        return $this->calculateAverageRate($currencyKeyA, $currencyKeyB, 'rate', $date);
    }


    public function getAveragePurchaseRateFiatBtc($currencyKeyA, $currencyKeyB, $date = null)
    {
        $avgPurchaseRateFiatBtc = $this->calculateAverageRate($currencyKeyA, $currencyKeyB, 'purchase_rate_fiat_btc', $date);
        return  $avgPurchaseRateFiatBtc;
    }



    /**
     * Returns the resulting revenue from all trades of the given currency pair
     * @param  string $currencyKeyA
     * @param  string $currencyKeyB
     * @param  string $rateField        a column in the db which contains a rate
     * @param  DateTime $date
     * @return float
     */
    public function getResultRevenue($currencyKeyA, $currencyKeyB, $rateField = 'rate', $date = null)
    {

        return  $this->getAverageTradeResults($currencyKeyA, $currencyKeyB, $rateField, $date)['resultValue'];
    }

    protected function getAverageTradeResults($currencyKeyA, $currencyKeyB, $rateField = 'rate', $date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        $amount = 0;
        $rate = 0;

        $trades = Trade::orderBy('date', 'asc')
                    ->where(function ($query) use ($currencyKeyA, $currencyKeyB, $date) {
                        $query->where('source_currency', '=', $currencyKeyA)
                               ->where('target_currency', '=', $currencyKeyB)
                               ->where('date', '<=', $date);
                    })
                    ->orWhere(function ($query) use ($currencyKeyA, $currencyKeyB, $date) {
                        $query->where('source_currency', '=', $currencyKeyB)
                               ->where('target_currency', '=', $currencyKeyA)
                               ->where('date', '<=', $date);
                    })
                    ->get();


        $buyVolume = 0;
        $sellVolume = 0;
        $buyRate = 0;
        $sellRate = 0;
        foreach ($trades as $trade) {
            $trade->addAll();
            if ($trade->type == 'buy') {
                if ($buyVolume == 0) {
                    $buyVolume = $trade->volume;
                    $buyRate = $trade->$rateField;

                } else {
                    $buyRate = Helper::getWeightedAverage($buyVolume, $buyRate, $trade->volume, $trade->$rateField);
                    $buyVolume += $trade->volume;
                }
            } elseif ($trade->type== 'sell') {

                if ($sellVolume == 0) {
                    $sellVolume = $trade->volume;
                    $sellRate = $trade->$rateField;
                } else {
                    $sellRate = Helper::getWeightedAverage($sellVolume, $sellRate, $trade->volume, $trade->$rateField);
                    $sellVolume += $trade->volume;
                }
            }
        }


        $resultSellValue = $sellVolume * $sellRate;
        $resultBuyValue = $buyVolume * $buyRate;

        return [
            'resultBuyRate' => $buyRate,
            'resultSellRate' => $sellRate,
            'resultBuyVolume' => $buyVolume,
            'resultSellVolume' => $sellVolume,
            'resultBuyValue' => $resultBuyValue,
            'resultSellValue' => $resultSellValue,
            'resultValue' => $resultSellValue - $resultBuyValue,
        ];
    }

    /**
     * Gets all current Volumes from all active platforms
     * @return Collection
     */
    public function getCurrentVolumes()
    {
        $adapters = $this->getActiveAdapters();
        $allVolumes = collect();
        foreach ($adapters as $adapter) {
            $volumes = $adapter->getCoinVolumes();
            foreach ($volumes as $key => $value) {
                if ($allVolumes->has($key)) {
                    $allVolumes[$key] = $allVolumes[$key] + $value;
                } else {
                    $allVolumes[$key] = $value;
                }
            }
        }
        return $allVolumes;
    }

    /**
     * Returns the current rate for a currency.
     * If multiple platforms deliver rates for the same currency
     * calculate the average value.
     * Returns -1 if the currency is not found on any plattform
     * @param  string $currencyKey
     * @param  string $currencyKey
     * @return float
     */
    public function getCurrentRate($currencyKeySource, $currencyKeyTarget)
    {
        $adapters = $this->getActiveAdapters();

        $hit = false;
        $resultRate = 0;
        foreach ($adapters as $adapter) {
            $rate = $adapter->getCurrentRate($currencyKeySource, $currencyKeyTarget);
            if ($rate != -1) {
                $hit = 1;
                if ($resultRate !== 0) {
                    $resultRate = ($resultRate + $rate) / 2;
                }
                $resultRate = $rate;
            }
        }
        if ($hit) {
            return $resultRate;
        } else {
            return -1;
        }
    }

 /**
  * Returns yesterdays's average rate for the given currency pair
  * @param  string $currencyKeySource
  * @param  string $currencyKeyTarget
  * @return Collection
  */
    public function getYesterdaysRate($currencyKeySource, $currencyKeyTarget)
    {
        return $this->getPastRate(1, 1, $currencyKeySource, $currencyKeyTarget);
    }

    /**
     * Returns a past day's average rate for a given currency pair
     * @param  integer $days
     * @param  string $currencyKeySource
     * @param  string $currencyKeyTarget
     * @return Collection
     */
    public function getPastRate($daysFrom, $daysTo, $currencyKeySource, $currencyKeyTarget)
    {

        $ratesRaw = Rate::where('date', '>', Carbon::today()->subDays($daysFrom))
                      ->where('date', '<', Carbon::tomorrow()->subdays($daysTo))
                      ->get();

        $ratesFiat = collect();
        $ratesBtc = collect();
        foreach ($ratesRaw as $rateRaw) {
                $rates = unserialize($rateRaw->rates);
            if (isset($rates[$currencyKeyTarget])) {

                $ratesFiat->push($rates[$currencyKeyTarget][1]);
                $ratesBtc->push($rates[$currencyKeyTarget][0]);
            }
        }
        $result = collect();
        if ($ratesFiatCount = $ratesFiat->count()) {
            $result->put('fiat', $ratesFiat->sum() / $ratesFiatCount);
            $result->put('btc', $ratesBtc->sum() / $ratesBtc->count());
        }
        return $result;


    }

    /**
     * Returns instances of all active adapter classes
     * @return array
     */
    public function getActiveAdapters()
    {
        $apis = explode(',', config('api.active'));
        $adapters = [];
        foreach ($apis as $apiKey) {
            $conf = config('api.adapters.' . $apiKey);
            $adapter = App::make($conf['adapterClass'], [$conf['key'], $conf['secret']]);
            $adapter->addWatchList(explode(',', config('api.watchList')));
            $adapters[] = $adapter;
        }
        return $adapters;
    }

    public function getSellVolume($sourceCurrencyKey, $targetCurrencyKey)
    {
        return Trade::where('source_currency', '=', $sourceCurrencyKey)
                          ->where('target_currency', '=', $targetCurrencyKey)
                          ->where('type', '=', 'sell')
                          ->selectRaw('sum(volume) as sum')
                          ->get()
                          ->first()
                          ->sum;
    }

    /**
     * Returns the sum of all BTC/Fiat trades
     * @return array
     */
    public function getSumBtcFiatTrades()
    {

        $trades =  Trade::where(function ($query) {
                              $query->where('target_currency', '=', config('api.fiat'))
                              ->where('source_currency', '=', 'BTC')
                              ->where('type', '=', 'sell');
        })
                          ->orWhere(function ($query) {
                              $query->where('source_currency', '=', config('api.fiat'))
                              ->where('target_currency', '=', 'BTC')
                              ->where('type', '=', 'buy');
                          })
                          ->get();

        $buyVolumeBtc = 0;
        $buyVolumeFiat = 0;
        $sellVolumeBtc = 0;
        $sellVolumeFiat = 0;

        foreach ($trades as $trade) {
            if ($trade->type == 'buy') {
                $buyVolumeFiat += $trade->rate * $trade->volume;
                $buyVolumeBtc += $trade->volume;
            } elseif ($trade->type == 'sell') {
                $sellVolumeFiat += $trade->rate * $trade->volume;
                $sellVolumeBtc += $trade->volume;
            }
        }


        $revenueBtc = $sellVolumeBtc - $buyVolumeBtc;
        $revenueFiat = $sellVolumeFiat - $buyVolumeFiat;
        return collect([
            'buyVolumeBtc' => $buyVolumeBtc,
            'sellVolumeBtc' => $sellVolumeBtc,
            'buyVolumeFiat' => $buyVolumeFiat,
            'sellVolumeFiat' => $sellVolumeFiat,
        ]);
    }



    /**
     * Requests all current rates from the api adapters
     * and stores them in the db
     * @return \App\Rate
     */
    public function storeCurrentRates() : Rate
    {
        $balances = collect();
        $volumes = $this->getCurrentVolumes();
        $rateBtcFiat = $this->getCurrentRate('BTC', config('api.fiat'));
        $rates = collect();

        foreach ($volumes as $currencyKey => $volume) {
            if ($currencyKey == 'BTC') {
                $rateBtc = 1;
                $rateFiat = $rateBtcFiat;

            } else {
                $rateBtc = $this->getCurrentRate('BTC', $currencyKey);
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

        return $rate;
    }

    public function getSellPool($sourceCurrency, $targetCurrency) {

        $trades = $this->getTradeHistory(null, null, $sourceCurrency);
        $trades = $trades->sortBy('date');

        $allSellTrades = $trades->filter(function ($item) use ($sourceCurrency, $targetCurrency) {
            return ($item->type == 'sell' && $item->source_currency == $sourceCurrency && $item->target_currency == $targetCurrency);
        });

        $allBuyTrades = $trades->filter(function($item) use ($sourceCurrency, $targetCurrency) {
            return ($item->type == 'buy' && $item->target_currency == $sourceCurrency && $item->source_currency == $targetCurrency);
        });



        $sellPool = collect();
        while (!$allSellTrades->isEmpty()) {
            $sellTrade = $allSellTrades->pop();
            $tradePool = new TradePool($sellTrade, $allBuyTrades);
            $sellPool->push($tradePool);
        }
        return $sellPool;
    }

/*
    public function getSellPool($key)
    {
        $trades = $this->getTradeHistory(null, null, $key);

        $sellPool = $trades->filter(function ($item) {
            return ($item->type == 'sell');
        })->sortByDesc('date');
        $buyPool = $trades->diff($sellPool)->sortByDesc('date');

        $sellResultPool = collect();
        $i=0;
        while (!$sellPool->isEmpty()) {
            $i++;
            $sellTrade = $sellPool->pop();
            $buyTrade = $buyPool->pop();
            if (!$buyTrade || $buyTrade->date > $sellTrade->date) {
                if ($buyTrade && $buyTrade->date > $sellTrade->date) {
                    $buyPool->push($buyTrade);
                }
                // We're selling more Coins than we bought, so we assume that we bought the diff for 0
                $buyTrade = App::make(Trade::class);
                $buyTrade->init(
                    $date = Carbon::now(),
                    $platformId = 'unkown',
                    $tradeId = substr(md5(rand()), 0, 7),
                    $type = 'buy',
                    $sourceCurrency = $sellTrade->target_currency_key,
                    $targetCurrency = $sellTrade->source_currency_key,
                    $rate = 0,
                    $volume = $sellTrade->volume,
                    $feeFiat = 0,
                    $feeCoin = 0
                );
            }
            $buyTradeAdded = $sellTrade->addBuyTrade($buyTrade);
            if ($sellTrade->volume > 0) {
                $sellPool->push($sellTrade);
            } else {
                $sellTrade->addSellResults();
                $sellResultPool->push($sellTrade);
            }
            if ($buyTradeAdded->volume > 0) {
                $buyPool->push($buyTradeAdded);
            }
        }
        return $sellResultPool;
    }
*/

    public function getcurrentBalanceInfo()
    {

        $balances = collect();
        $volumes = $this->getCurrentVolumes();
        $rateBtcFiat = $this->getCurrentRate('BTC', config('api.fiat'));

        foreach ($volumes as $currencyKey => $volume) {

            $item = collect();
            $item->put('currency', $currencyKey);
            $item->put('volume', $volume);

            // Current and past rates
            if ($currencyKey == 'BTC') {
                $rateFiat = $rateBtcFiat;
                $rateBtc = 1;

                $yesterdaysRate = $this->getYesterdaysRate(config('api.fiat'), $currencyKey);
                $sevenDaysAgoRate = $this->getPastRate(7, 7, config('api.fiat'), $currencyKey);

            } else {
                $rateBtc = $this->getCurrentRate('BTC', $currencyKey);
                $rateFiat = $rateBtcFiat * $rateBtc;
                $yesterdaysRate = $this->getYesterdaysRate(config('api.fiat'), $currencyKey);
                $sevenDaysAgoRate = $this->getPastRate(7, 7, config('api.fiat'), $currencyKey);
            }

            $yesterdaysRateFiat = $yesterdaysRate->get('fiat');
            $yesterdaysRateBtc = $yesterdaysRate->get('btc');
            $sevenDaysAgoRateFiat = $sevenDaysAgoRate->get('fiat');
            $sevenDaysAgoRateBtc = $sevenDaysAgoRate->get('btc');

            if ($yesterdaysRateFiat) {
                $rateDiffDayFiat =  ($rateFiat - $yesterdaysRateFiat) / $yesterdaysRateFiat  * 100;
            } else {
                $rateDiffDayFiat = 0;
            }
            if ($yesterdaysRateBtc) {
                $rateDiffDayBtc = ($rateBtc - $yesterdaysRateBtc) / $yesterdaysRateBtc * 100;
            } else {
                $rateDiffDayBtc = 0;
            }
            if ($sevenDaysAgoRateFiat) {
                $rateDiffSevenDaysAgoFiat = ($rateFiat - $sevenDaysAgoRateFiat) / $sevenDaysAgoRateFiat * 100;
            } else {
                $rateDiffSevenDaysAgoFiat = 0;
            }
            if ($sevenDaysAgoRateBtc) {
                $rateDiffSevenDaysAgoBtc = ($rateBtc - $sevenDaysAgoRateBtc) / $sevenDaysAgoRateBtc * 100;
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

            // Average purchase rates
            if ($currencyKey == 'BTC') {
                $item->put('averagePurchaseRateBtcCoin', 1);
            } else {
                $item->put('averagePurchaseRateBtcCoin', $this->getAveragePurchaseRateBtcCoin('BTC', $currencyKey));
            }
            if ($currencyKey == 'BTC') {
                $item->put('averagePurchaseRateFiatBtc', $this->getAveragePurchaseRateFiatBtc(config('api.fiat'), 'BTC'));
            } else {
                $item->put('averagePurchaseRateFiatBtc', $this->getAveragePurchaseRateFiatBtc('BTC', $currencyKey));
            }

            $item->put('averagePurchaseRateCoinFiat', $item['averagePurchaseRateFiatBtc'] * $item['averagePurchaseRateBtcCoin']);
            $item->put('purchaseValueBtc', $volume * $item['averagePurchaseRateBtcCoin']);
            $item->put('purchaseValueFiat', $volume * $item['averagePurchaseRateBtcCoin'] * $item['averagePurchaseRateFiatBtc']);

            if ($currencyKey == 'BTC') {
                $item->put('sellVolume', $this->getSellVolume('BTC', config('api.fiat')));
            } else {
                $item->put('sellVolume', $this->getSellVolume($currencyKey, 'BTC'));
            }
            $item->put('revenueFiat', $item['currentValueFiat'] - $item['purchaseValueFiat']);
            $item->put('revenueBTC', $item['currentValueBtc'] - $item['purchaseValueBtc']);

            if ($item['purchaseValueFiat'] > 0) {
                $revenueRate = (100 / $item['purchaseValueFiat'] * $item['revenueFiat']);
            } else {
                $revenueRate = 0;
            }
            $item->put('revenueRate', $revenueRate);
            $balances->push($item);

        }

        return $balances;
    }
}
