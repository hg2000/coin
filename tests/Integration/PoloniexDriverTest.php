<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \App\Driver\DriverInterface;
use \App\Driver\Poloniex\PoloniexDriver;

class PoloniexDriverTest extends AbstractDriverTest
{
    /**
    * @var driverInterface
    */
    protected $driver;

    public function __construct()
    {
        parent::__construct();
        $this->setDriver(new PoloniexDriver(config('connections.poloniex.key'), config('connections.poloniex.secret')));
        $this->currency1 = 'BTC';
        $this->currency2 = 'MAID';
    }
}
