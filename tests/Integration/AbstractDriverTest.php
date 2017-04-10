<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;

use \App\Driver\DriverInterface;
use \App\TradeInterface;
use \App\Driver\Bitcoinde\BitcoindeDriver;
use \Carbon\Carbon;

abstract class AbstractDriverTest extends TestCase
{

    use \Tests\CreatesApplication;

    protected $currency1 = 'BTC';
    protected $currency2 = 'EUR';

    public function __construct()
    {
        parent::__construct();

        $this->createApplication();
    }

    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
        return $this;
    }
    /**
     * @test
     */
    public function a_driver_returns_a_rate_for_a_currency_pair()
    {

        $rate = $this->driver->getCurrentRate($this->currency1, $this->currency2);
        $this->assertTrue(is_numeric($rate));
    }


    /**
     * @test
     */
    public function a_driver_returns_the_avaible_coin_volume_of_a_currency()
    {
            $volume = $this->driver->getCoinVolume($this->currency1);
            $this->assertTrue(is_numeric($volume));
    }

    /**
     * @test
     */
    public function a_driver_returns_a_trade_history()
    {

        $trades = $this->driver->getTradeHistory(Carbon::create(2010), Carbon::now());
        $trade = $trades->first();
        $this->assertTrue($trade instanceof TradeInterface);
    }

    /**
     * @test
     */
    public function a_driver_returns_a_collection_of_currency_volume_pairs()
    {
        $volumes = $this->driver->getCoinVolumes();
        $this->assertTrue($volumes instanceof Collection);
        $keys = $volumes->keys()->first();
        $this->assertTrue(
            is_string(
                $volumes->keys()
                ->first()
            )
        );
    }

    /**
     * @test
     */
    public function a_driver_returns_a_current_rate_or_minus_one()
    {
        $rate = $this->driver->getCurrentRate('dcjdcbsdhe3dhbfv', 'cdsjcnsdcezf');
        $this->assertEquals($rate, -1);
    }
}
