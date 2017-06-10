<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Service\Helper;
use App\Trade;

/**
 * A Trade Pool consists of a sell trade and the correspondig buy trades.
 * The Revenue will be calculated by last in first out.
 */
class TradePool extends Model
{

    protected $sellTrade;
    protected $buyTrades;

    protected $allBuyTrades;
    protected $allSellTrades;

    protected $originalSellVolume;

    public function __construct(Trade $sellTrade, $allBuyTrades)
    {
        $this->sellTrade = $sellTrade;
        $this->allBuyTrades = $allBuyTrades->sortBy('date');

        $this->buyTrades = collect();

        $this->originalSellVolume = $sellTrade->volume;
        $transferVolume = 1;

        $sellVolume = $this->sellTrade->volume;

        while ($sellVolume > 0) {
            $buyTrade = $this->allBuyTrades->pop();
            if (!$buyTrade) {
                // We are selling more than we bought, so we assume, that we got the diff for free
                $buyTrade = new Trade();
                $buyTrade->init(
                    $date = $sellTrade->date,
                    $platformId = $sellTrade->plaform_id,
                    $tradeId = 'virtual_' . $sellTrade->tradeId,
                    $type = 'buy',
                    $sourceCurrency = $sellTrade->target_currency,
                    $targetCurrency = $sellTrade->source_currency,
                    $rate = 0,
                    $volume = $sellVolume,
                    $feeFiat = 0,
                    $feeCoin = 0,
                    $purchaseRateBtcFiat = 0

                );
            }
            $sellVolume = $buyTrade->takeVolume($sellVolume);
            $this->buyTrades->push($buyTrade);
        }

        $this->calculateRevenue();

        $this->setAttribute('sell_trade', $this->sellTrade);
        $this->setAttribute('buy_trades', $this->buyTrades);
    }

    public function calculateRevenue()
    {
        if ($this->buyTrades) {
            $sumPurchaseValueTakenBtc = 0;
            foreach ($this->buyTrades as $buyTrade) {
                $sumPurchaseValueTakenBtc += $buyTrade->purchase_value_taken_btc;
            }

            $this->sellTrade->revenue_btc = $this->sellTrade->value_btc - $sumPurchaseValueTakenBtc;

            $sumPurchaseValueTakenFiat = 0;
            foreach ($this->buyTrades as $buyTrade) {
                $sumPurchaseValueTakenFiat += $buyTrade->purchase_value_taken_fiat;
            }
            $this->sellTrade->revenue_fiat = $this->sellTrade->value_fiat - $sumPurchaseValueTakenFiat;
        }
        return $this;
    }
}
