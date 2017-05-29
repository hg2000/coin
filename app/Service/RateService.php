<?php
namespace App\Service;

use \App;
use \App\Trade;
use \App\Rate;
use \App\Mail\Alert;
use \Illuminate\Support\Facades\Mail;

/**
 * Deals with rate related tasks
 */
class RateService
{
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
