<?php
namespace App\Service;

use \App;
use \App\Trade;
use \App\Service\Helper;
use \Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TradingService
{
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
        $lastUpdate = Trade::orderBy('updated_at', 'asc')->take(1)->get()->first();

        if (empty($lastUpdate)) {
            $this->updateTradeHistory(true);
        } else {
            $lastUpdate = $lastUpdate->updated_at;
            $diff = $lastUpdate->diffInMinutes(Carbon::now());
            if ($diff >= config('api.cacheDuration')) {
                $this->updateTradeHistory(false);
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
     * param boolean  foreces the refresh of the whole trade history
     */
    public function updateTradeHistory($forceRefresh = false)
    {
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

        $drivers = $this->getActiveDrivers();

        foreach ($drivers as $driver) {

            $trades = $driver->getTradeHistory($from, Carbon::now());

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
                    Trade::insert($trade->toArray());
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
    protected function updatePurchaseRatesFiatBtc()
    {
        $trades = Trade::orderBy('date', 'asc')
                       ->get();

        foreach ($trades as $trade) {
            $trade->addAll();
            if ($trade->source_currency == config('api.fiat') && $trade->target_currency == 'BTC' && $trade->type == 'buy') {
                $rate = $trade->rate;
            } else {
                $rate = $this->getAverageRate(config('api.fiat'), 'BTC', 'purchase_rate_fiat_btc', $trade->date);
            }
            Trade::where('id', '=', $trade->id)
            ->update(['purchase_rate_fiat_btc' => $rate]);
        }
        return $this;
    }

    /**
     * Returns the average purchase rate
     * for all purchases up to the given date.
     * @param  string $currencyKeyA
     * @param  string $currencyKeyB
     * @param  DateTime $date
     * @return float
     */
    public function getAveragePurchaseRate($currencyKeyA, $currencyKeyB, $date = null)
    {
        return $this->getAverageRate($currencyKeyA, $currencyKeyB, 'rate', $date);
    }



    /**
     * Returns the average purchase rate for all fiat/btc trades
     * up to the given date. This is usefull for calculating the
     * actual value in fiat for a coin/btc trade
     * @param  string $currencyKey
     * @param  DateTime $date
     * @return float
     */
    public function getAveragePurchaseRateFiatBtc($currencyKey, $date = null)
    {

        return $this->getAverageRate($currencyKey, 'BTC', 'purchase_rate_fiat_btc', $date);

    }


    /**
     * Returns the weighted average rate of the given rate field
     * @param  string $currencyKeyA
     * @param  string $currencyKeyB
     * @param  string $rateField        a column in the db which contains a rate
     * @param  DateTime $date
     * @return float
     */
    public function getAverageRate($currencyKeyA, $currencyKeyB, $rateField, $date = null)
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
                               ->where('date', '<', $date);
                    })
                    ->orWhere(function ($query) use ($currencyKeyA, $currencyKeyB, $date) {
                        $query->where('source_currency', '=', $currencyKeyB)
                               ->where('target_currency', '=', $currencyKeyA)
                               ->where('date', '<', $date);
                    })
                    ->get();


        foreach ($trades as $trade) {
            $trade->addAll();
            if ($trade->type == 'buy') {


                if ($amount == 0) {
                    $amount = $trade->volume;
                    $rate = $trade->$rateField;

                } else {
                    $rate = Helper::getWeightedAverage($amount, $rate, $trade->volume, $trade->$rateField);
                    $amount += $trade->volume;
                }
            } elseif ($trade->type== 'sell') {
                    $amount -= $trade->volume;
            }
        }
        return $rate;
    }

    /**
     * Gets all current Volumes from all active platforms
     * @return Collection
     */
    public function getCurrentVolumes()
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
     * Returns instances of all active driver classes
     * @return array
     */
    public function getActiveDrivers()
    {
        $apis = explode(',', config('api.active'));
        $drivers = [];
        foreach ($apis as $apiKey) {
            $conf = config('api.drivers.' . $apiKey);
            $driver = App::make($conf['driverClass'], [$conf['key'], $conf['secret']]);
            $driver->addWatchList(explode(',', config('api.watchList')));
            $drivers[] = $driver;
        }
        return $drivers;
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
