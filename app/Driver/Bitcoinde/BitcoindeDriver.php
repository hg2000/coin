<?php

namespace App\Driver\Bitcoinde;

use \Illuminate\Support\Collection;
use \App\Trade;
use \Carbon\Carbon;

class BitcoindeDriver implements \App\Driver\DriverInterface
{
    /**
     * Connector to Bitcon.de
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
        $tradesResponse = $this->request(Connector::METHOD_SHOW_MY_TRADES, [
            'state' => 1,
            'page' => 1
        ]);
        if (!empty($tradesResponse['errors'])) {
            throw new \Exception($tradesResponse['errors']);
        }

        $maxPages = $tradesResponse['page']['last'];

        $rawTrades = [];
        $pageCount = 1;
        $yearCounter = 60;
        while ($pageCount < $maxPages+1) {

            sleep(0.10);
            $result = $this->request(Connector::METHOD_SHOW_MY_TRADES, [

            'state' => 1,
            'page' => $pageCount
            ]);

            if (!isset($result['trades'])) {
                throw new \Exception($result['errors']);
            }
            $rawTrades = array_merge($rawTrades, $result['trades']);


            $pageCount++;
        }
        $tradeCollection = collect([]);
        foreach ($rawTrades as $rawTrade) {

            /*
            / Bitcoin.de does not deliver the date fro certain old trades
            / so we set a very early date but keep the delivered order
             */

            if (isset($rawTrade['successfully_finished_at'])) {
                $date = $rawTrade['successfully_finished_at'];
                $date = new Carbon($date);
                $date = $date->toDateTimeString();
            } else {
                $date = Carbon::now()->subYears($yearCounter);
                $yearCounter--;
            }


            if (!($rawTrade['type'] == 'buy' || $rawTrade['type'] == 'sell')) {
                throw new \Exception('bitcoin.de API error: Invalid Trade type: ' . $rawTrade['type']);
            }

            $sourceCurrency = $rawTrade['type'] == 'buy' ? 'EUR' : 'BTC';
            $targetCurrency = $rawTrade['type'] == 'buy' ? 'BTC' : 'EUR';

            $trade = new Trade();
            $trade->init(
                $date,
                $platformId = 'bitcoin.de',
                $tradeId = $rawTrade['trade_id'],
                $type =  $rawTrade['type'],
                $sourceCurrency,
                $targetCurrency,
                $rate = $rawTrade['price'],
                $volume = $rawTrade['amount'],
                $feeFiat = $rawTrade['fee_eur'],
                $feeCoin = $rawTrade['fee_btc']
            );
            if ($rawTrade['type'] == 'buy') {
                $trade->purchaseRateBtcFiat = $rawTrade['price'];
            }
            $tradeCollection->push($trade);
        }


        return $tradeCollection;

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
        $currencyPair = $currencyKeySource . $currencyKeyTarget;
        if ($currencyPair != 'BTCEUR') {
            return -1;
        }
        $rates = $this->request(Connector::METHOD_SHOW_RATES);
        return $rates['rates']['rate_weighted'];

    }

    /**
     * Returns the current rate of all avaible coins
     * @param  string $sourceCurrencyKey
     * @param  string $targetCurrencyKey
     * @return float
     */
    public function getCurrentRates() : Collection
    {
        return collect(['BTCEUR' => $this->getCurrentRate('BTCEUR')]);

    }

    /**
     * Returns the avaible volume of a a given currency
     * @param  string $currencyKey
     * @return float
     */
    public function getCoinVolume($currencyKey) : float
    {
        if ($currencyKey != 'BTC') {
            throw new \Exception('Bitcoin.de driver only supports the currency for volume request. "BTC".' . $currencyKey . ' is not allowed.');
        }

        $result = $this->request(Connector::METHOD_SHOW_ACCOUNT_INFO);
        if (!isset($result['data']['btc_balance']['available_amount'])) {
            throw new \Exception('Bitcoin.de API Error. The API did not return the current btc volume.');
        }

        return $result['data']['btc_balance']['available_amount'];
    }

    /**
     * Returns a collection of all volumes of avaible coins
     *  @return Collection of array with [CurrencyKey => Volume] pairs
     */
    public function getCoinVolumes() : Collection
    {
        return collect(['BTC' => $this->getCoinVolume('BTC') ]);
    }

    /**
     * Requests the API
     * @param  string $method
     * @param  array $arguments
     * @return
     */
    protected function request($method, array $arguments = [])
    {
        try {
            $result = $this->connector->doRequest($method, $arguments);
            if (!empty($result['errors'])) {
                throw new \Exception($result['errors']);
            } else {
                return $result;
            }
        } catch (\Exception $e) {
            throw new \Exception('Bitcoin.de API error: ' . $e->getMessage());
        }
    }

    /**
     * Bitcoin.de handles BTC only. So a watchlist is useless here
     * @var array
     */
    public function addWatchList(array $watchList)
    {
        return null;
    }
}
