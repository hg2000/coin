<?php
namespace App\Driver;

use \Illuminate\Support\Collection;

interface DriverInterface
{


    /**
     * Returns the history of all trades in the given time period
     * @param  Datetime $from
     * @param  Datetime $to
     * @return \Illuminate\Support\Collection  Collection of \App\TradeInterface Objects
     */
    public function getTradeHistory(\DateTime $from, \DateTime $to) : Collection;

    /**
     * Returns the current rate of the given currency key pair
     * Returns -1 if the currency does not exist
     * @param  string $currencyKey  Source e.g. "BTC"
     * @param  string $currencyKey  Target e.g. "BTC"
     * @return float
     */
    public function getCurrentRate($currencyKeySource, $currencyKeyTarget) : float;


    /**
     * Returns the avaible volume of a a given currency
     * @param  string $currencyKey
     * @return float
     */
    public function getCoinVolume($currencyKey) : float;

    /**
     * Returns a collection of all volumes of avaible coins
     * @return Collection of arrays with [CurrencyKey => Volume] pairs
     */
    public function getCoinVolumes() : Collection;
}
