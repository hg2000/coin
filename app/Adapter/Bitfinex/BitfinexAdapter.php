<?php
namespace App\Adapter\Bittrex;

use \Illuminate\Support\Collection;
use App\Trade;
use \Carbon\Carbon;

class BitfinexAdapter implements \App\Adapter\AdapterInterface
{
    /**
     * @var App\Adapther\Bitfinex\Connector
     */
    protected $connector;

    public function __constructor() {
        $this->connector = new Connector(config('api.adapters.bitfinex.key'), config('api.adapters.bitfinex.secret'));
    }
    /**
     * Returns the history of all trades in the given time period
     * @param  Datetime $from
     * @param  Datetime $to
     * @return \Illuminate\Support\Collection  Collection of \App\TradeInterface Objects
     */
    public function getTradeHistory(\DateTime $from, \DateTime $to) : Collection {
        return new Collection();
    }

    /**
     * Returns the current rate of the given currency key pair
     * Returns -1 if the currency does not exist
     * @param  string $currencyKey  Source e.g. "BTC"
     * @param  string $currencyKey  Target e.g. "BTC"
     * @return float
     */
    public function getCurrentRate($currencyKeySource, $currencyKeyTarget) : float {

        $response = $this->connector->getTicker($currencyKeySource . $currencyKeyTarget);
        dd($response);

        return 0;
    }


    /**
     * Returns the avaible volume of a a given currency
     * @param  string $currencyKey
     * @return float
     */
    public function getCoinVolume($currencyKey) : float {
        return 0;
    }

    /**
     * Returns a collection of all volumes of avaible coins
     * @return Collection of arrays with [CurrencyKey => Volume] pairs
     */
    public function getCoinVolumes() : Collection {
        return new Collection();
    }

    /**
     * Adds a list of coins which sould always taken into account in the result sets
     * @param array $watchList      List of currency keys
     */
    public function addWatchList(array $watchList) {

    }

}
