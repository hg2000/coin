<?php

namespace App\Driver\Poloniex;

use \Illuminate\Support\Collection;
use \App\Trade;
use \Carbon\Carbon;

class PoloniexDriver implements \App\Driver\DriverInterface
{

    protected $rates;

    protected $volumes;

    protected $watchList = [];

    /**
     * Connector to Poloniex
     * @var Connector
     */
    protected $connector;

    public function __construct($key, $secret)
    {
        $this->connector = new Connector($key, $secret);

    }

    /**
     * Returns the history of all trades in the given time period
     * @param  Datetime            $from
     * @param  Datetime            $to
     * @return \Illuminate\Support\Collection  Collection of \App\TradeInterface Objects
     */
    public function getTradeHistory(\DateTime $from, \DateTime $to) : Collection
    {

        $volumes = $this->getCoinVolumes();
        $history = collect();
        foreach ($volumes as $currency => $volume) {
            if ($currency != 'BTC') {

                $items = $this->connector->get_my_trade_history('BTC_' . $currency, $from->getTimestamp(), $to->getTimestamp());
                if (isset($items['error'])) {
                    throw new \Exception('Poloniex API error: ' . $items['error']);
                }
                if ($items) {
                    foreach ($items as $item) {
                        if (!($item['type'] == 'buy' || $item['type'] == 'sell')) {
                            throw new \Exception('poloniex API error: Invalid Trade type: ' . $item['type']);
                        }
                        if ($item['type'] == 'buy') {
                            $sourceCurrency = 'BTC';
                            $targetCurrency = $currency;
                        }
                        if ($item['type'] == 'sell') {
                            $sourceCurrency = $currency;
                            $targetCurrency = 'BTC';
                        }
                        $trade = new Trade();
                        $trade->init(
                            $date = $item['date'],
                            $platformId = 'poloniex',
                            $tradeId = $item['tradeID'],
                            $type = $item['type'],
                            $sourceCurrency,
                            $targetCurrency,
                            $rate = $item['rate'],
                            $volume = $item['amount'],
                            $feeFiat = 0,
                            $feeCoin = $item['fee']
                        );
                                           ;
                        $history->push($trade);
                    }
                }
                sleep(0.25);
            }
        }

        return $history;
    }

    /**
     * Returns the current rate of the given currency key pair
     * Returns -1 if the currency does not exist
     * @param  string $currencyKey e.g. "BTC"
     * @param  string $currencyKey e.g. "BTC"
     * @return float
     */
    public function getCurrentRate($currencyKeySource, $currencyKeyTarget) : float
    {
        $currencyPair = $currencyKeySource . '_' . $currencyKeyTarget;

        if (!$this->rates) {
            $this->rates = $this->connector->get_ticker();
            if (isset($this->rates['error'])) {
                throw new \Exception('Poloniex API error: ' . $this->rates['error']);
            }
        }
        $rate =  $this->rates[$currencyPair] ?? -1;

        if ($rate == -1) {
            return -1;
        }
        $high24h = $rate['high24hr'];
        $low24h = $rate['low24hr'];
        return ($high24h + $low24h) / 2;
    }


    /**
     * Returns the avaible volume of a a given currency
     * @param  string $currencyKey
     * @return float
     */
    public function getCoinVolume($currencyKey) : float
    {
        $volumes = $this->getCoinVolumes();
        if (!$volumes->has($currencyKey)) {
            return (float)$volumes->get($currencyKey);
        } else {
            return 0;
        }
    }

    /**
     * Returns a collection of all volumes of avaible coins
     *  @return Collection of array with [CurrencyKey => Volume] pairs
     */
    public function getCoinVolumes() : Collection
    {
        if (!$this->volumes) {
            $balances = $this->connector->get_balances();
            
            if (isset($balances['error'])) {
                throw new \Exception('Poloniex API error: ' . $balances['error']);
            }

            $volumes = collect();
            foreach ($balances as $key => $volume) {
                if ($volume > 0 || in_array($key, $this->watchList)) {
                    $volumes->put($key, $volume);
                }

            }
        }
        return $volumes;
    }

    /**
     * Adds a list of coins which sould always taken into account in the result sets
     * @param array $watchList      List of currency keys
     */
    public function addWatchList(array $watchList)
    {
        $this->watchList = $watchList;
    }
}
