<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Service\Helper;

class Trade extends Model implements TradeInterface
{

    public function init(
        $date,
        $platformId,
        $tradeId,
        $type,
        $sourceCurrency,
        $targetCurrency,
        $rate,
        $volume,
        $feeFiat,
        $feeCoin,
        $purchaseRateBtcFiat = null
    ) {
        $this->setAttribute('date', $date);
        $this->setAttribute('platform_id', $platformId);
        $this->setAttribute('trade_id', $tradeId);
        $this->setAttribute('type', $type);
        $this->setAttribute('source_currency', $sourceCurrency);
        $this->setAttribute('target_currency', $targetCurrency);
        $this->setAttribute('rate', $rate);
        $this->setAttribute('volume', $volume);
        $this->setAttribute('original_volume', $volume);
        $this->setAttribute('fee_fiat', $feeFiat);
        $this->setAttribute('fee_coin', $feeCoin);
    }

    public function getVolumeValueBTCAttribute()
    {
        if ($this->target_currency == config('api.fiat') && $this->type == 'sell') {
            return $this->volume;
        }
        if ($this->source_currency == config('api.fiat') && $this->target_currency == 'BTC' && $this->type == 'buy') {
            return $this->volume;
        }
        return $this->rate * $this->volume;
    }
    public function getVolumeValueFiatAttribute()
    {
        if ($this->target_currency == config('api.fiat') && $this->type == 'sell') {
            return $this->rate * $this->volume;
        }

        if ($this->target_currency == 'BTC' && $this->type == 'sell') {
            return $this->rate * $this->volume * $this->purchase_rate_fiat_btc;
        }

        if ($this->source_currency == config('api.fiat') && $this->target_currency == 'BTC' && $this->type == 'buy') {
            return $this->rate * $this->volume;
        }
        return $this->rate * $this->volume * $this->purchase_rate_fiat_btc;
    }

    public function addAll()
    {
        $this->setAttribute('value_btc', $this->getVolumeValueBTCAttribute());
        $this->setAttribute('value_fiat', $this->getVolumeValueFiatAttribute());
        return $this;
    }



    /**
     * Adds the corresponding buy trades a to sell trade
     * and the volume of the buy trade
     * @param Trade $trade
     */
    public function addBuyTrade(Trade $buyTrade)
    {
        if ($this->type != 'sell') {
            throw new \Exception('Attempt to add a buy trade info to trade which is not of type "sell"');
        }

        $remainingBuyVolume = $buyTrade->volume - $this->volume;
        $buyTrade->setAttribute('volume_before', $buyTrade->volume);
        if ($remainingBuyVolume >= 0) {
            $buyTrade->setAttribute('volume_taken', $this->volume);
            $buyTrade->volume = $remainingBuyVolume;
            $this->volume_taken += $this->volume;
            $this->volume = 0;
        } else {
            $buyTrade->setAttribute('volume_taken', $buyTrade->volume);
            $this->volume_taken += $buyTrade->volume;
            $buyTrade->volume = 0;
            $this->volume = $remainingBuyVolume * -1;
        }

        $buyTrade->setAttribute('purchase_value_taken_btc', $buyTrade->rate * $buyTrade->volume_taken);
        $buyTrade->setAttribute('purchase_value_taken_fiat', $buyTrade->rate * $buyTrade->volume_taken * $buyTrade->purchase_rate_fiat_btc);
        $buyTrade->setAttribute('value_taken_btc',  $buyTrade->volume_taken * $this->rate);
        $buyTrade->setAttribute('revenue_taken_btc',  $buyTrade->value_taken_btc - $buyTrade->purchase_value_taken_btc);

        if ($this->getAttribute('buy_pool')) {
            $this->buy_pool->push($buyTrade);
        } else {
            $buyPool = collect();
            $buyPool->push($buyTrade);
            $this->setAttribute('buy_pool', $buyPool);
        }
        return $buyTrade;
    }

    /**
     * If it is a sell trade and has buy trades assigned,
     * return the average buy price for all assigned buy trades
     */
    protected function getAvgBuyRate()
    {
        if ($this->type != 'sell' || !$this->buy_pool) {
            return false;
        }
        $buyVolume = 0;

        foreach ($this->buy_pool as $buyTrade) {

            if ($buyVolume == 0) {
                $buyVolume = $buyTrade->volume_taken;
                $avgBuyRate = $buyTrade->rate;

            } else {
                $avgBuyRate = Helper::getWeightedAverage($buyVolume, $avgBuyRate, $buyTrade->volume_taken, $buyTrade->rate);
                $buyVolume += $buyTrade->volume_taken;
            }
        }

        return $avgBuyRate;

    }
    public function addSellResults()
    {
        $avgBuyRate = $this->getAvgBuyRate();
        $this->setAttribute('avg_buy_rate', $this->getAvgBuyRate());
        $this->setAttribute('buy_value', $avgBuyRate * $this->volume_taken);
        $this->setAttribute('revenue', $this->volume_taken * $this->rate - $this->buy_value) ;

        return $this;
    }


    /**
     * Reduces the volume by the given amount and returns the transfer volume
     *
     * @param  float $volume
     * @return float    transfer volume
     */
    public function takeVolume($volume) {
        if (!$this->type == 'buy') {
            throw new \Exception('Trying to take volume from trade which is not of type "buy"');
        }
        $this->setAttribute('volume_before_taken', $this->volume);
        $this->setAttribute('volume_taken', $volume);
        $this->volume -= $volume;
        if ($this->volume < 0 ){
            $transferVolume = $this->volume *-1;
            $this->volume = 0;
        } else {
            $transferVolume = 0;
        }

        $this->value_btc = $this->volume * $this->rate;
        $this->value_fiat = $this->volume * $this->rate * $this->purchase_rate_fiat_btc;

        $this->purchase_value_taken_btc = $this->volume_taken * $this->rate;
        $this->purchase_value_taken_fiat = $this->volume * $this->rate * $this->purchase_rate_fiat_btc;

        return $transferVolume;



    }
}
