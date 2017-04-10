<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \App\Driver\DriverInterface;
use \App\Driver\Bitcoinde\BitcoindeDriver;

class BitcoindeDriverTest extends AbstractDriverTest
{
    /**
    * @var driverInterface
    */
    protected $driver;

    public function __construct()
    {
        parent::__construct();
        $this->setDriver(new BitcoindeDriver(config('connections.bitcoinde.key'), config('connections.bitcoinde.secret')));
        $this->currency1 = 'BTC';
        $this->currency2 = 'EUR';
    }




}
