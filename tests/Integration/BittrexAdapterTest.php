<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use \Carbon\Carbon;

use \App\Adapter\AdapterInterface;
use \App\Adapter\Bittrex\BittrexAdapter;

class BitrexAdapterTest extends AbstractAdapterTest
{
    /**
    * @var adapterInterface
    */
    protected $adapters;

    public function __construct()
    {
        parent::__construct();
        $this->setAdapter(new BittrexAdapter(config('api.adapters.bittrex.key'), config('api.adapters.bittrex.secret')));
        $this->currency1 = 'BTC';
        $this->currency2 = 'WAVES';
    }
}
