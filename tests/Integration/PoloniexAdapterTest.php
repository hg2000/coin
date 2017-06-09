<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \App\Adapter\AdapterInterface;
use \App\Adapter\Poloniex\PoloniexAdapter;

class PoloniexAdapterTest extends AbstractAdapterTest
{
    /**
    * @var adapterInterface
    */
    protected $adapters;

    public function __construct()
    {
        parent::__construct();
        $this->setAdapter(new PoloniexAdapter(config('api.adapters.poloniex.key'), config('api.adapters.poloniex.secret')));
        $this->currency1 = 'BTC';
        $this->currency2 = 'MAID';
    }
}
