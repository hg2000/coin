<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

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
        $this->setAttribute('fee_fiat', $feeFiat);
        $this->setAttribute('fee_coin', $feeCoin);
        $this->setAttribute('purchase_rate_fiat_btc', $purchaseRateBtcFiat);
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

    public function setPurchaseRateBtcFiatAttribute($value) {

        $this->purchase_rate_fiat_btc = $value;
        return $this;
    }



}
