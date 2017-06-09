<?php
namespace App\Adapter\Bittrex;

use \Illuminate\Support\Collection;
use App\Trade;
use \Carbon\Carbon;

class BittrexAdapter implements \App\Adapter\AdapterInterface
{
    protected $connector;

    protected $watchList = [];

    public function __construct($key, $secret)
    {
        $this->connector = new Connector($key, $secret);
    }


    /**
     * Returns the history of all trades in the given time period
     * @param  Datetime $from
     * @param  Datetime $to
     * @return \Illuminate\Support\Collection  Collection of \App\TradeInterface Objects
     */
    public function getTradeHistory(\DateTime $from, \DateTime $to) : Collection {

        $items = $this->connector
             ->request('history')
             ->result
             ;

        $result = collect();
        foreach ($items as $item) {

            if ($item->OrderType == 'LIMIT_BUY') {
                $type = 'buy';
            }
            if ($item->OrderType == 'LIMIT_SELL') {
                $type = 'sell';
            }
            //TODO: add the other possible responses for type

            $currencyPair = explode('-', $item->Exchange);

            $trade = new Trade();
            $trade->init(
                $date = Carbon::parse($item->TimeStamp),
                $platformId = 'Bittrex',
                $tradeId = $item->OrderUuid,
                $type,
                $sourceCurrency = $currencyPair[0],
                $targetCurrency = $currencyPair[1],
                $rate = $item->PricePerUnit,
                $volume = $item->Quantity,
                $feeFiat = 0,
                $feeCoin = 0,
                $purchaseRateBtcFiat = null
            );
            $result->push($trade);
        }

        return $result;
    }

    /**
     * Returns the current rate of the given currency key pair
     * Returns -1 if the currency does not exist
     * @param  string $currencyKey  Source e.g. "BTC"
     * @param  string $currencyKey  Target e.g. "BTC"
     * @return float
     */
    public function getCurrentRate($currencyKeySource, $currencyKeyTarget) : float {
        try {
            return $this->connector
            ->request('ticker', $currencyKeySource, $currencyKeyTarget)
            ->result
            ->Last
            ;
        } catch (\Exception $e) {
            if ($e->getCode() === 891) {
                return -1;
            } else {
                throw $e;
            }
        }
    }


    /**
     * Returns the avaible volume of a a given currency
     * @param  string $currencyKey
     * @return float
     */
    public function getCoinVolume($currencyKey) : float {
        $volumes = $this->getCoinVolumes();
        if ($volumes->has($currencyKey)) {
            return (float)$volumes->get($currencyKey);
        } else {
            return 0;
        }
    }

    /**
     * Returns a collection of all volumes of avaible coins
     * @return Collection of arrays with [CurrencyKey => Volume] pairs
     */
    public function getCoinVolumes() : Collection {

        //TODO support watchlist

        $items =  $this->connector
                        ->request('balances')
                        ->result;

        $result = collect();
        foreach ($items as $item) {

            $result[$item->Currency] = $item->Balance;
        }
        return $result;
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
