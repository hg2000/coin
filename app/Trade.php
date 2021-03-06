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
}
