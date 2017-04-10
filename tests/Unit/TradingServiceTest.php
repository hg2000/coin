<?php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use App\Trade;
use Illuminate\Support\Facades\DB;

class TradingServiceTest extends \Tests\TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate', ['--database' => 'mysql_testing']);
        Artisan::call('db:seed', ['--database' => 'mysql_testing']);


    }

    /**
     * @test
     */
    public function average_btc_rate()
    {
        DB::table('trades')->truncate();
        DB::table('trades')->insert([
              'date' => '1970-04-08 16:39:15',
              'platform_id' => 'bitcoin.de',
              'trade_id' => str_random(10),
              'source_currency' => 'EUR',
              'target_currency' => 'BTC',
              'rate' => '100',
              'volume' => '1',
              'fee_fiat' => 0,
              'fee_coin' => 0,
              'purchase_rate_btc_fiat' => '100',
              'type' => 'buy',
          ]);

          DB::table('trades')->insert([
              'date' => '1971-04-08 16:39:15',
              'platform_id' => 'bitcoin.de',
              'trade_id' => str_random(10),
              'source_currency' => 'BTC',
              'target_currency' => 'EUR',
              'rate' => '50',
              'volume' => '1',
              'fee_fiat' => 0,
              'fee_coin' => 0,
              'purchase_rate_btc_fiat' => '100',
              'type' => 'sell',
          ]);

          DB::table('trades')->insert([
                'date' => '1972-04-08 16:39:15',
                'platform_id' => 'bitcoin.de',
                'trade_id' => str_random(10),
                'source_currency' => 'EUR',
                'target_currency' => 'BTC',
                'rate' => '100',
                'volume' => '1',
                'fee_fiat' => 0,
                'fee_coin' => 0,
                'purchase_rate_btc_fiat' => '50',
                'type' => 'buy',
            ]);



        $trades = Trade::OrderBy('date')->get();
        $trading = new \App\Service\TradingService();

        $rate = $trading->getAverageBtcRate();
        $this->assertEquals(100, $rate);

    }
}
