<?php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use App;
use App\Trade;
use App\Rate;
use App\Service\TradingService;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class TradingServiceTest extends \Tests\TestCase
{
    use DatabaseMigrations;

    protected $date;

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate', ['--database' => 'mysql_testing']);
        Artisan::call('db:seed', ['--database' => 'mysql_testing']);

        $this->date = Carbon::create('1970');
    }

    protected function buyTrade($volume, $rate = 100, $source = 'EUR', $target = 'BTC', $purchaseRateFiatBtc = 100)
    {
        $this->date = $this->date->addDay();
        return  [
              'updated_at' =>  $this->date,
              'date' => $this->date,
              'platform_id' => 'bitcoin.de',
              'trade_id' => str_random(10),
              'source_currency' => $source,
              'target_currency' => $target,
              'rate' => $rate,
              'volume' => $volume,
              'fee_fiat' => 0,
              'fee_coin' => 0,
              'purchase_rate_fiat_btc' => $purchaseRateFiatBtc,
              'type' => 'buy',
          ];
    }
    protected function sellTrade($volume, $rate = 100, $source='BTC', $target='EUR', $purchaseRateFiatBtc = 100)
    {
        $this->date = $this->date->addDay();
        return [
              'updated_at' =>  $this->date,
              'date' => $this->date,
              'platform_id' => 'bitcoin.de',
              'trade_id' => str_random(10),
              'source_currency' => $source,
              'target_currency' => $target,
              'rate' => $rate,
              'volume' => $volume,
              'fee_fiat' => 0,
              'fee_coin' => 0,
              'purchase_rate_fiat_btc' => $purchaseRateFiatBtc,
              'type' => 'sell',
          ];
    }

    /**
     * @test
     */
    public function getResultRevenue()
    {
        $trading = new \App\Service\TradingService();

        DB::table('trades')->truncate();

        DB::table('trades')->insert($this->buyTrade(1, 100));
        $result = $trading->getResultRevenue('BTC', 'EUR');
        $this->assertEquals(-100, $result);

        DB::table('trades')->insert($this->buyTrade(1, 100));
        $result = $trading->getResultRevenue('BTC', 'EUR');
        $this->assertEquals(-200, $result);

        DB::table('trades')->insert($this->sellTrade(1, 400));
        $result = $trading->getResultRevenue('BTC', 'EUR');
        $this->assertEquals(200, $result);

        DB::table('trades')->insert($this->buyTrade(1, 100));
        $result = $trading->getResultRevenue('BTC', 'EUR');
        $this->assertEquals(100, $result);

        DB::table('trades')->insert($this->sellTrade(1, 100));
        $result = $trading->getResultRevenue('BTC', 'EUR');
        $this->assertEquals(200, $result);


        DB::table('trades')->insert($this->buyTrade(1, 5, 'BTC', 'ETH'));
        $result = $trading->getResultRevenue('BTC', 'ETH');
        $this->assertEquals(-5, $result);

        DB::table('trades')->insert($this->buyTrade(1, 10, 'BTC', 'ETH'));
        $result = $trading->getResultRevenue('BTC', 'ETH');
        $this->assertEquals(-15, $result);

        DB::table('trades')->insert($this->sellTrade(1, 10, 'ETH', 'BTC'));
        $result = $trading->getResultRevenue('BTC', 'ETH');
        $this->assertEquals(-5, $result);

        DB::table('trades')->insert($this->buyTrade(1, 5, 'BTC', 'ETH'));
        $result = $trading->getResultRevenue('BTC', 'ETH');
        $this->assertEquals(-10, $result);

    }

    /**
     * @test
     */
    public function storeCurrentRates()
    {
        $trading = App::make(TradingService::class);
        $rate = $trading->storeCurrentRates();
        $this->assertTrue($rate instanceof Rate);
    }

    /**
     * @test
     */
    public function getYesterdaysRate()
    {
        $trading = App::make(TradingService::class);
        DB::table('rates')->insert([
            'date' => Carbon::yesterday()->addHours(8),
            'rates' => 'a:18:{s:3:"BTC";a:2:{i:0;i:1;i:1;d:1213.8652536;}s:4:"ARDR";a:2:{i:0;d:3.5380000000000003E-5;i:1;d:0.042946552672368001;}s:4:"DASH";a:2:{i:0;d:0.068383470000000002;i:1;d:83.008318153597997;}s:3:"DCR";a:2:{i:0;d:0.011452895000000001;i:1;d:13.902271293629173;}s:3:"DGB";a:2:{i:0;d:8.6000000000000002E-7;i:1;d:0.0010439241180960001;}s:3:"ETH";a:2:{i:0;d:0.051816569999999999;i:1;d:62.89833388373215;}s:3:"FCT";a:2:{i:0;d:0.0059008000000000003;i:1;d:7.1627760884428797;}s:4:"GAME";a:2:{i:0;d:0.00065410500000000009;i:1;d:0.7939953317060281;}s:3:"LTC";a:2:{i:0;d:0.011411595;i:1;d:13.852138658655491;}s:4:"MAID";a:2:{i:0;d:0.00019761;i:1;d:0.23987191276389599;}s:4:"NEOS";a:2:{i:0;d:0.00068606500000000003;i:1;d:0.83279046521108402;}s:3:"NXC";a:2:{i:0;d:8.8750000000000002E-5;i:1;d:0.107730541257;}s:3:"NXT";a:2:{i:0;d:2.1815E-5;i:1;d:0.026480470507283999;}s:3:"REP";a:2:{i:0;d:0.011325;i:1;d:13.747023997019999;}s:2:"SC";a:2:{i:0;d:6.8999999999999996E-7;i:1;d:0.00083756702498399989;}s:5:"STRAT";a:2:{i:0;d:0.00051292000000000004;i:1;d:0.62261576587651202;}s:3:"SYS";a:2:{i:0;d:3.7605000000000003E-5;i:1;d:0.045647402861628002;}s:3:"XMR";a:2:{i:0;d:0.017107890000000001;i:1;d:20.766673233410906;}}',

        ]);
        DB::table('rates')->insert([
            'date' => Carbon::yesterday()->addHours(16),
            'rates' => 'a:18:{s:3:"BTC";a:2:{i:0;i:1;i:1;d:1219.0960144000001;}s:4:"ARDR";a:2:{i:0;d:3.6529999999999998E-5;i:1;d:0.044533577406031997;}s:4:"DASH";a:2:{i:0;d:0.071594989999999997;i:1;d:87.281166960007852;}s:3:"DCR";a:2:{i:0;d:0.011764980000000001;i:1;d:14.342640227495714;}s:3:"DGB";a:2:{i:0;d:8.2499999999999994E-7;i:1;d:0.0010057542118800001;}s:3:"ETH";a:2:{i:0;d:0.051825980000000001;i:1;d:63.180845660374118;}s:3:"FCT";a:2:{i:0;d:0.0059878750000000001;i:1;d:7.2997945472254004;}s:4:"GAME";a:2:{i:0;d:0.00066799000000000003;i:1;d:0.81434394665905607;}s:3:"LTC";a:2:{i:0;d:0.011672704999999998;i:1;d:14.23014814276695;}s:4:"MAID";a:2:{i:0;d:0.00020128499999999999;i:1;d:0.24538574125850401;}s:4:"NEOS";a:2:{i:0;d:0.00068767500000000001;i:1;d:0.83834185170252007;}s:3:"NXC";a:2:{i:0;d:8.850000000000001E-5;i:1;d:0.10788999727440002;}s:3:"NXT";a:2:{i:0;d:2.2235E-5;i:1;d:0.027106599880184001;}s:3:"REP";a:2:{i:0;d:0.011997885;i:1;d:14.626573784729544;}s:2:"SC";a:2:{i:0;d:8.0499999999999992E-7;i:1;d:0.000981372291592;}s:5:"STRAT";a:2:{i:0;d:0.00053542999999999993;i:1;d:0.652740578990192;}s:3:"SYS";a:2:{i:0;d:3.8899999999999997E-5;i:1;d:0.047422834960159997;}s:3:"XMR";a:2:{i:0;d:0.017511495000000002;i:1;d:21.348193760685533;}}',
        ]);
        $rate = $trading->getYesterdaysRate('EUR', 'BTC');
        $this->assertEquals(1216.480634, $rate->get('fiat'));
        $this->assertEquals(1, $rate->get('btc'));
    }


    /**
     * @test
     */
    public function getAveragePurchaseRateBtcCoin() {

        DB::table('trades')->truncate();
        $trading = App::make(TradingService::class);

        $trade = $this->buyTrade(1, 100);
        DB::table('trades')->insert($trade);

        $trade = $this->buyTrade(1, 200);
        DB::table('trades')->insert($trade);

        $trade = $this->sellTrade(1, 150);
        DB::table('trades')->insert($trade);

        $trade = $this->buyTrade(1, 150);
        DB::table('trades')->insert($trade);

        $rate = $trading->getAveragePurchaseRateBtcCoin('EUR', 'BTC');
        $this->assertEquals(150, $rate);
    }

    /**
     * @test
     */
    public function getAveragePurchaseRateFiatBtc() {

        DB::table('trades')->truncate();
        $trading = App::make(TradingService::class);

        $trade = $this->buyTrade($volume = 1, $rate = 100, $source = 'EUR', $target = 'BTC', $purchaseRateFiatBtc = 100);
        DB::table('trades')->insert($trade);

        $trade = $this->buyTrade($volume = 1, $rate = 0.5, $source = 'BTC', $target = 'ETH', $purchaseRateFiatBtc = 100);
        DB::table('trades')->insert($trade);

        $rate = $trading->getAveragePurchaseRateBtcCoin('EUR', 'BTC');
    }

    /**
     * @test
     */
    public function getSumBtcFiatTrades() {

        DB::table('trades')->truncate();
        $trading = App::make(TradingService::class);

        $trade = $this->buyTrade(1, 100);
        DB::table('trades')->insert($trade);

        $trade = $this->sellTrade(2, 200);
        DB::table('trades')->insert($trade);

        $trading->getSumBtcFiatTrades();

    }
}
