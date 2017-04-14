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
    public function getAveragePurchaseRate()
    {
        $trading = new \App\Service\TradingService();

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
              'purchase_rate_fiat_btc' => null,
              'type' => 'buy',
          ]);

         $result = $trading->getAveragePurchaseRate('BTC', 'EUR');
         $this->assertEquals(100, $result);


          DB::table('trades')->insert([
                'date' => '1970-04-09 16:39:15',
                'platform_id' => 'bitcoin.de',
                'trade_id' => str_random(10),
                'source_currency' => 'EUR',
                'target_currency' => 'BTC',
                'rate' => '200',
                'volume' => '1',
                'fee_fiat' => 0,
                'fee_coin' => 0,
                'purchase_rate_fiat_btc' => null,
                'type' => 'buy',
            ]);

            $result = $trading->getAveragePurchaseRate('BTC', 'EUR');
            $this->assertEquals(150, $result);

            DB::table('trades')->insert([
                  'date' => '1970-04-10 16:39:15',
                  'platform_id' => 'bitcoin.de',
                  'trade_id' => str_random(10),
                  'source_currency' => 'BTC',
                  'target_currency' => 'EUR',
                  'rate' => '200',
                  'volume' => '1',
                  'fee_fiat' => 0,
                  'fee_coin' => 0,
                  'purchase_rate_fiat_btc' => null,
                  'type' => 'sell',
              ]);

              $result = $trading->getAveragePurchaseRate('BTC', 'EUR');
              $this->assertEquals(150, $result);


             DB::table('trades')->insert([
                'date' => '1970-04-11 16:39:15',
                'platform_id' => 'bitcoin.de',
                'trade_id' => str_random(10),
                'source_currency' => 'EUR',
                'target_currency' => 'BTC',
                'rate' => '50',
                'volume' => '1',
                'fee_fiat' => 0,
                'fee_coin' => 0,
                'purchase_rate_fiat_btc' => null,
                'type' => 'buy',
             ]);
              $result = $trading->getAveragePurchaseRate('BTC', 'EUR');
              $this->assertEquals(100, $result);

              DB::table('trades')->insert([
                    'date' => '1970-04-12 16:39:15',
                    'platform_id' => 'bitcoin.de',
                    'trade_id' => str_random(10),
                    'source_currency' => 'BTC',
                    'target_currency' => 'EUR',
                    'rate' => '200',
                    'volume' => '2',
                    'fee_fiat' => 0,
                    'fee_coin' => 0,
                    'purchase_rate_fiat_btc' => null,
                    'type' => 'sell',
                ]);

                $result = $trading->getAveragePurchaseRate('BTC', 'EUR');
                $this->assertEquals(100, $result);

                DB::table('trades')->insert([
                   'date' => '1970-04-13 16:39:15',
                   'platform_id' => 'bitcoin.de',
                   'trade_id' => str_random(10),
                   'source_currency' => 'EUR',
                   'target_currency' => 'BTC',
                   'rate' => '500',
                   'volume' => '1',
                   'fee_fiat' => 0,
                   'fee_coin' => 0,
                   'purchase_rate_fiat_btc' => null,
                   'type' => 'buy',
                ]);
                 $result = $trading->getAveragePurchaseRate('BTC', 'EUR');
                 $this->assertEquals(500, $result);


                DB::table('trades')->insert([
                   'date' => '1970-04-14 16:39:15',
                   'platform_id' => 'bitcoin.de',
                   'trade_id' => str_random(10),
                   'source_currency' => 'BTC',
                   'target_currency' => 'ETH',
                   'rate' => '5',
                   'volume' => '1',
                   'fee_fiat' => 0,
                   'fee_coin' => 0,
                   'purchase_rate_fiat_btc' => null,
                   'type' => 'buy',
                ]);
                 $result = $trading->getAveragePurchaseRate('BTC', 'ETH');
                 $this->assertEquals(5, $result);

                DB::table('trades')->insert([
                   'date' => '1970-04-15 16:39:15',
                   'platform_id' => 'bitcoin.de',
                   'trade_id' => str_random(10),
                   'source_currency' => 'BTC',
                   'target_currency' => 'ETH',
                   'rate' => '10',
                   'volume' => '1',
                   'fee_fiat' => 0,
                   'fee_coin' => 0,
                   'purchase_rate_fiat_btc' => null,
                   'type' => 'buy',
                ]);

                 $result = $trading->getAveragePurchaseRate('BTC', 'ETH');
                 $this->assertEquals(7.5, $result);


                DB::table('trades')->insert([
                   'date' => '1970-04-16 16:39:15',
                   'platform_id' => 'bitcoin.de',
                   'trade_id' => str_random(10),
                   'source_currency' => 'ETH',
                   'target_currency' => 'BTC',
                   'rate' => '10',
                   'volume' => '1',
                   'fee_fiat' => 0,
                   'fee_coin' => 0,
                   'purchase_rate_fiat_btc' => null,
                   'type' => 'sell',
                ]);

                 $result = $trading->getAveragePurchaseRate('BTC', 'ETH');
                 $this->assertEquals(7.5, $result);

                 DB::table('trades')->insert([
                    'date' => '1970-04-17 16:39:15',
                    'platform_id' => 'bitcoin.de',
                    'trade_id' => str_random(10),
                    'source_currency' => 'BTC',
                    'target_currency' => 'ETH',
                    'rate' => '7.5',
                    'volume' => '1',
                    'fee_fiat' => 0,
                    'fee_coin' => 0,
                    'purchase_rate_fiat_btc' => null,
                    'type' => 'buy',
                 ]);

                  $result = $trading->getAveragePurchaseRate('BTC', 'ETH');
                  $this->assertEquals(7.5, $result);
    }

    

}
