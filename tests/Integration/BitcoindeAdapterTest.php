<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \App\Adapter\AdapterInterface;
use \App\Adapter\Bitcoinde\BitcoindeAdapter;

class BitcoindeAdapterTest extends AbstractAdapterTest
{
    /**
    * @var adapterInterface
    */
    protected $adapter;

    public function __construct()
    {
        parent::__construct();
        $this->setAdapter(new BitcoindeAdapter(config('api.adapters.bitcoinde.key'), config('api.adapters.bitcoinde.secret')));
        $this->currency1 = 'BTC';
        $this->currency2 = 'EUR';
    }
}
