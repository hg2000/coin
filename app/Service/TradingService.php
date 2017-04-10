<?php
namespace App\Service;

use \App;
use \App\Trade;
use \Carbon\Carbon;

class TradingService
{

    /**
     * Gets all current Volumes from all active platforms
     * @return Collection
     */
    public function getVolumes()
    {
        $drivers = $this->getActiveDrivers();
        $allVolumes = collect();
        foreach ($drivers as $driver) {
            $volumes = $driver->getCoinVolumes();
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
        $drivers = $this->getActiveDrivers();

        $hit = false;
        $resultRate = 0;
        foreach ($drivers as $driver) {
            $rate = $driver->getCurrentRate($currencyKeySource, $currencyKeyTarget);
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
     * Returns the history of all trades
     * TODO Implement time filter!
     *
     * @param  DateTime $from
     * @param  DateTime $to
     * @return Collection
     */
    public function getTradeHistory($from = null, $to = null)
    {

        if (!$from) {
            $from = Carbon::create(1970);
        }
        if (!$to) {
            $to = Carbon::now();
        }
         $lastUpdate = Trade::orderBy('updated_at', 'asc')
                                ->take(1)
                                ->get()
                                ->first();
        if (!$lastUpdate) {
            $this->updateTradeHistory();
        } else {
            $lastUpdate = $lastUpdate->updated_at;
            $diff = $lastUpdate->diffInMinutes(Carbon::now());
            if ($diff >= config('api.cacheDuration')) {
                $this->updateTradeHistory();
            }
        }

        $trades = Trade::orderBy('date', 'desc')
                ->get();

        $trades = $trades->map(function ($item) {
            return $item->addAll();
        });

        return $trades;
    }

    /**
     * Stores the trade history in the database
     */
    public function updateTradeHistory()
    {
        Trade::truncate();
        $drivers = $this->getActiveDrivers();
        foreach ($drivers as $driver) {
            $trades = $driver->getTradeHistory(Carbon::create(0), Carbon::now());
            foreach ($trades as $trade) {
                $trade->created_at = Carbon::now();
                $trade->updated_at = Carbon::now();
                $trade->volume = $trade->volume - $trade->fee_coin;
                Trade::insert($trade->toArray());
            }
        }
        $this->updatePurchaseRates();
    }

    /**
     * Calculates the BTC/Fiat rate on date of purchase for
     * all trades which do not have the value set yet
     */
    protected function updatePurchaseRates()
    {

        $trades = Trade::orderBy('date', 'desc')
                ->get();

        $trades = $trades->map(function ($item) {
            return $item->addAll();
        });

        $trades = $trades->reverse();
        foreach ($trades as $trade) {

            if ($trade->purchase_rate_btc_fiat == 0) {

                $rate= $this->getAverageBtcRate($trade->date);
                Trade::where('id', '=', $trade->id)
                    ->update(['purchase_rate_btc_fiat' => $rate]);
            }
        }
        return $this;
    }

    /**
     * Returns instances of all active driver classes
     * @return array
     */
    public function getActiveDrivers()
    {
        $apis = explode(',', config('api.active'));
        $drivers = [];
        foreach ($apis as $apiKey) {
            $conf = config('api.drivers.' . $apiKey);
            $drivers[] = App::make($conf['driverClass'], [$conf['key'], $conf['secret']]);
        }
        return $drivers;
    }

    /**
     * Calculates the weighted average of two fractions
     */
    protected function getWeightedAvgSum($aVolume, $aAvg, $bVolume, $bAvg)
    {
        return ($aVolume * $aAvg + $bVolume * $bAvg) / ($aVolume + $bVolume);
    }

    /**
     * Returns the average BTC/EUR rate
     * for all BTC buys up to the given date.
     * @var Dateime
     */
    public function getAverageBtcRate($date = null)
    {
        if (!$date) {
            $date = Carbon::now();
        }
        $amount = 0;
        $rate = 0;

        $trades = Trade::orderBy('date', 'asc')
                ->where(function ($query) {
                    $query->where('source_currency', '=', config('api.fiat'))
                          ->orWhere('target_currency', '=', config('api.fiat'));
                })
                ->where('date', '<', $date)
                ->get();

        foreach ($trades as $trade) {
            $trade->addAll();
            if ($trade->type == 'buy') {
                if ($amount == 0) {
                    $amount = $trade->valueBTC;
                    $rate = $trade->rate;
                } else {
                    $rate = $this->getWeightedAvgSum($amount, $rate, $trade->valueBTC, $trade->rate);
                    $amount += $trade->valueBTC;
                }
            } elseif ($trade->type== 'sell') {
                if ($amount - $trade->valueBTC <= 0) {
                    $rate = 0;
                    $amount = 0;
                } else {
                    $amount -= $trade->valueBTC;
                }
            }
        }

        return $rate;
    }

    /**
    * Returns the Average buy rate for a currency
    * @param  string $currency
    * @return boolean
    */
    public function getAverageBuyRate($sourceCurrencyKey, $targetCurrencyKey)
    {
        if ($sourceCurrencyKey == $targetCurrencyKey) {
            return 1;
        }

        $trades = Trade::where('target_currency', '=', $targetCurrencyKey)
                          ->where('source_currency', '=', $sourceCurrencyKey)
                          ->orderBy('date')
                          ->get();

        $amount = 0;
        $avgRate = 0;
        foreach ($trades as $trade) {
            $rate = $trade['rate'];
            $tradeAmount = $trade['volume'];

            if ($trade['type'] == 'buy') {
                $avgRate = $this->getWeightedAvgSum($amount, $avgRate, $tradeAmount, $rate);
                $amount += $tradeAmount;
            } elseif ($trade['type'] == 'sell') {
                $amount -= $tradeAmount;
            }
        }
        return $avgRate;
    }

    /**
    * Returns the Average buy rate for a currency
    * @param  string $currency
    * @return boolean
    */
    public function getAverageSellRate($sourceCurrencyKey, $targetCurrencyKey)
    {
        if ($sourceCurrencyKey == $targetCurrencyKey) {
            return 1;
        }

        $trades = Trade::where('source_currency', '=', $sourceCurrencyKey)
                          ->where('target_currency', '=', $targetCurrencyKey)
                          ->orderBy('date')
                          ->get();
        $amount = 0;
        $avgRate = 0;
        foreach ($trades as $trade) {
            $rate = $trade['rate'];
            $tradeAmount = $trade['volume'];

            if ($trade['type'] == 'sell') {
                if ($amount != 0) {
                    $avgRate = $this->getWeightedAvgSum($amount, $avgRate, $tradeAmount, $rate);
                    $amount += $tradeAmount;
                }
            }
        }

        return $avgRate;
    }


    /**
     * Gets the average rate for all purchased bitcoin on time of the Trade
     * This is useful to calculate the actual fiat price paid for a coin.
     * @param  string $currency
     * @return boolean
     */
    public function getAvgPurchaseRate($currency)
    {
        $trades = Trade::where(
                            function($query) use ($currency) {
                                $query->where('target_currency', '=', $currency)
                                      ->where('source_currency', '=', 'BTC');
                                  })
                        ->orWhere(
                            function($query) use ($currency) {
                                $query->where('target_currency', '=', 'BTC')
                                      ->where('source_currency', '=', $currency);
                            })
                          ->orderBy('date')
                          ->get();

        $amount = 0;
        $avgRate = 0;
        foreach ($trades as $trade) {
            $rate = $trade['purchase_rate_btc_fiat'];
            $tradeAmount = $trade['volume'];

            if ($trade['type'] == 'buy') {
                $avgRate = $this->getWeightedAvgSum($amount, $avgRate, $tradeAmount, $rate);
                $amount += $tradeAmount;
            } elseif ($trade['type'] == 'sell') {
                $amount -= $tradeAmount;
            }
        }
        return $avgRate;

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


        return collect([
            'buyVolumeBtc' => $buyVolumeBtc,
            'sellVolumeBtc' => $sellVolumeBtc,
            'buyVolumeFiat' => $buyVolumeFiat,
            'sellVolumeFiat' => $sellVolumeFiat,
        ]);
    }

}
