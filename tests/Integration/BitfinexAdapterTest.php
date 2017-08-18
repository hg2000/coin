<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \App\Adapter\AdapterInterface;
use \App\Adapter\Bitfinex\BitfinexAdapter;

class BitfinexAdapterTest extends AbstractAdapterTest
{
    /**
    * @var adapterInterface
    */
    protected $adapter;

    public function __construct()
    {
        parent::__construct();
        $this->setAdapter(new BitfinexAdapter(config('api.adapters.bitfinex.key'), config('api.adapters.bitfinex.secret')));
        $this->currency1 = 'BTC';
        $this->currency2 = 'EUR';
    }
}
