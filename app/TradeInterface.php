<?php
namespace App;

interface TradeInterface
{
    public function init(
        /**
         * @var DateTime   date of trade
         */
        $date,
        /**
         * @var string   Id of the trading platform
         */
        $platformId,
        /**
         * @var string   Id of the trade delivered by the platform
         */
        $tradeId,
        /**
         * @var string   Type of trade. "buy" or "sell"
         */
        $type,
        /**
         * @var string   Source currency key  e.g. "BTC", "ETH", "EUR"
         */
        $sourceCurrency,
        /**
         *  @var string  Target currency key e.g. "BTC", "ETH", "EUR"
         */
        $targetCurrency,
        /**
         * @var float   rate source currency / target currency
         */
        $rate,
        /**
         * @var float   Volume of coins
         */
        $volume,
        /**
         * @var float   fee paid in fiat
         */
        $feeFiat,
        /**
         * @var float   fee paid in coin
         */
        $feeCoin,
        /**
         * @var float   Optional. Should be set on BTC/Fiat trades
         */
        $purchaseRateBtcFiat = null
    );
}
