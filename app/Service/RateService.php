<?php
namespace App\Service;

use \App;
use \App\Trade;
use \App\Rate;
use \App\Mail\Alert;
use \Illuminate\Support\Facades\Mail;
use \App\Service\CacheService;
use \Illuminate\Support\Collection;

/**
 * Deals with rate related tasks
 */
class RateService
{
    /**
     * @var App\Service\CacheService
     */
    protected $cache;


    public function __construct()
    {
        $this->cache = App::make(CacheService::class);
    }

    public function analyseRateChanges()
    {

        $trading = App::make(TradingService::class);
        $balances = $trading->getcurrentBalanceInfo();

        $ratesRaw = Rate::orderBy('date', 'desc')->take(2)->get();
        $ratesRaw = $ratesRaw[1];

        $lastRates = unserialize($ratesRaw->rates);
        $diffItems = collect();

        foreach ($lastRates as $key => $values) {

            $pastRateBtc = $values[0];
            $pastRateFiat = $values[1];

            if (isset($lastRates[$key])) {
                $balance = $balances->filter(function ($localItem) use ($key) {
                    return $localItem['currency'] == $key;
                })->first();

                if ($balance) {
                    $currentRateBtc = $balance['currentRateBtc'];
                    $currentRateFiat = $balance['currentRateFiat'];
                    $diffBtc = (integer)floor(($currentRateBtc - $pastRateBtc) / $pastRateBtc * 100);
                    $diffFiat = (integer)floor(($currentRateFiat - $pastRateFiat) / $pastRateFiat * 100);

                    $diffItem = collect();
                    $diffItem->put('currency', $key);
                    $diffItem->put('diffBtc', $diffBtc);
                    $diffItem->put('diffFiat', $diffFiat);
                    $diffItems->push($diffItem);
                }
            }
        }
        return [ $diffItems, $ratesRaw->date ];
    }


    /**
     * Returns a collection of days with the average rate of each day
     * @param  DateTime $from
     * @param  DateTime $to
     */
    public function getDailyRateAverage(\DateTime $from, \DateTime $to)
    {

        $dayRateCollection = [];
        $ratesRaw = Rate::where('date', '>=', $from)
                        ->where('date', '<=', $to)->get();
        $rates = $ratesRaw->map(function ($item) {
            $item->rates = unserialize($item->rates);
            return $item;
        });

        $ndate = clone($from);
        $oneDay = new \DateInterval('P1D');
        while ($ndate < $to) {

            $dayRates = $rates->filter(function ($item) use ($ndate) {
                $from = clone($ndate);
                $from->setTime(0, 0);
                $to = clone($ndate)->setTime(23, 59);
                $date = \DateTime::createFromFormat('Y-m-d h:i:s', $item->date);

                return ($date >= $from && $date <= $to);
            });

            $averageDayRates = $this->reduceToAverage($dayRates);
            $dayRate = [];
            $dayRate['date'] = clone($ndate);
            $dayRate['date'] = $dayRate['date']->format(trans('global.dateFormat'));
            $dayRate['rates'] = $averageDayRates->toArray();
            $dayRateCollection[] = $dayRate;
            $ndate = $ndate->add($oneDay);
        }

        return $dayRateCollection;
    }


    /**
     * Reduces a collection of rates to an average value
     * @return Illuminate\Support\Collection
     */
    public function reduceToAverage(Collection $ratesCollection)
    {
        $amount = $ratesCollection->count();
        if ($amount == 0) {
            return $ratesCollection;
        }

        $reducedRatesCollection = $ratesCollection->reduce(function ($carry, $item) {
            $item->rates = collect($item->rates);

            $mappedRates = $item->rates->map(function ($rate, $key) use ($carry) {
                if ($carry) {
                    if (isset($carry->rates[$key])) {
                        if ($rate[0] != 1) {
                            $rate[0] += $carry->rates[$key][0];
                        }
                        if ($rate[1] != 1) {
                            $rate[1] += $carry->rates[$key][1];
                        }
                    }
                }
                return $rate;
            });

            $item->rates = $mappedRates;
            return $item;
        });

        $reducedRatesCollection = $reducedRatesCollection->rates->map(function ($item) use ($amount) {
            if ($item[0] != 0 && $item[0] != 1) {
                $item[0] = $item[0] / $amount;
            }
            if ($item[1] != 1 && $item[1] != 1) {
                $item[1] = $item[1] / $amount;
            }
            return $item;
        });

        return $reducedRatesCollection;
    }

    public function rateChangeAlert()
    {
        $alertChangeRate = config('api.alertChangeRate');

        $rateService = App::make(RateService::class);
        $rateChanges = $rateService->analyseRateChanges();
        $date = $rateChanges[1];
        $diffItems = $rateChanges[0];

        $increasesBtc = collect();
        $decreasesBtc = collect();

        $increasesBtc = $diffItems->filter(
            function ($item) use ($alertChangeRate) {
                if ($item['currency'] == 'BTC') {
                    if ($item['diffFiat'] >= $alertChangeRate) {
                        return $item;
                    }
                } else {
                    if ($item['diffBtc'] >= $alertChangeRate) {
                        return $item;
                    }
                }
            }
        );

        $decreasesBtc = $diffItems->filter(
            function ($item) use ($alertChangeRate) {
                if ($item['currency'] == 'BTC') {
                    if (($item['diffFiat']) <= $alertChangeRate * -1) {
                        return $item;
                    }
                } else {
                    if (($item['diffFiat']) <= $alertChangeRate * -1) {
                        return $item;
                    }
                }
            }
        );

        if (!$increasesBtc->isEmpty() || !$decreasesBtc->isEmpty()) {
            Mail::to(config('api.mail.alert.receiver.adress'))->send(new Alert($alertChangeRate, $increasesBtc, $decreasesBtc, $date));
        }
    }
}
