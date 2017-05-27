<?php
namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use App;

use App\Service\RateService;
use Illuminate\Support\Facades\DB;
use \Carbon\Carbon;

class RateServiceTest extends \Tests\TestCase
{
    protected $date;

    public function setUp()
    {
        $this->date = Carbon::create('1970');
    }

    protected function rate1()
    {

        return [
            'date' => $this->date->addDay(),
            'rates' => 'a:21:{s:3:"BTC";a:2:{i:0;i:1;i:1;d:1810.60313675;}s:4:"ARDR";a:2:{i:0;d:6.4195000000000005E-5;i:1;d:0.11623166836366626;}s:4:"DASH";a:2:{i:0;d:0.050860000000000002;i:1;d:92.087275535105007;}s:3:"DGB";a:2:{i:0;d:5.4750000000000001E-6;i:1;d:0.0099130521737062494;}s:3:"ETH";a:2:{i:0;d:0.061005005000000001;i:1;d:110.45585341044944;}s:3:"FCT";a:2:{i:0;d:0.0050650000000000001;i:1;d:9.1707048876387507;}s:3:"FLO";a:2:{i:0;d:3.5784999999999997E-5;i:1;d:0.064792433248598749;}s:4:"GAME";a:2:{i:0;d:0.0014767949999999999;i:1;d:2.6738896593367163;}s:3:"LSK";a:2:{i:0;d:0.00038171;i:1;d:0.69112532332884247;}s:3:"LTC";a:2:{i:0;d:0.013362044999999999;i:1;d:24.193360590394654;}s:4:"MAID";a:2:{i:0;d:0.00017935999999999999;i:1;d:0.32474977860748;}s:4:"NEOS";a:2:{i:0;d:0.00117289;i:1;d:2.1236383130627075;}s:3:"NXC";a:2:{i:0;d:8.585E-5;i:1;d:0.15544027928998749;}s:3:"REP";a:2:{i:0;d:0.0086449500000000002;i:1;d:15.652573587046913;}s:2:"SC";a:2:{i:0;d:4.2000000000000004E-6;i:1;d:0.0076045331743500007;}s:4:"SJCX";a:2:{i:0;d:0.00030499999999999999;i:1;d:0.55223395670874997;}s:5:"STRAT";a:2:{i:0;d:0.00095498000000000002;i:1;d:1.7290897835335151;}s:3:"SYS";a:2:{i:0;d:4.4085000000000002E-5;i:1;d:0.079820439283623756;}s:3:"XEM";a:2:{i:0;d:0.00011504000000000001;i:1;d:0.20829178485172001;}s:3:"XMR";a:2:{i:0;d:0.017033940000000001;i:1;d:30.841705195211297;}s:3:"XRP";a:2:{i:0;d:0.00017419499999999998;i:1;d:0.31539801340616619;}}'
        ];
    }

    protected function rate2()
    {
        return [
            'date' => $this->date->addDay(),
            'rates' => 'a:19:{s:3:"BTC";a:2:{i:0;i:1;i:1;d:1759.3265943599999;}s:4:"ARDR";a:2:{i:0;d:6.4985000000000002E-5;i:1;d:0.1143298387344846;}s:4:"DASH";a:2:{i:0;d:0.050817519999999998;i:1;d:89.404614395421177;}s:3:"DGB";a:2:{i:0;d:4.0299999999999995E-6;i:1;d:0.0070900861752707985;}s:3:"ETH";a:2:{i:0;d:0.062031744999999999;i:1;d:109.13409867305795;}s:3:"FCT";a:2:{i:0;d:0.0052190099999999996;i:1;d:9.1819430892307832;}s:3:"FLO";a:2:{i:0;d:3.5479999999999999E-5;i:1;d:0.0624209075678928;}s:4:"GAME";a:2:{i:0;d:0.00130462;i:1;d:2.2952526615339433;}s:3:"LSK";a:2:{i:0;d:0.00036171;i:1;d:0.63636602244595564;}s:3:"LTC";a:2:{i:0;d:0.013853565;i:1;d:24.372945331194892;}s:4:"MAID";a:2:{i:0;d:0.00018386500000000002;i:1;d:0.32347858427200143;}s:4:"NEOS";a:2:{i:0;d:0.00116938;i:1;d:2.0573213329126969;}s:3:"NXC";a:2:{i:0;d:8.5654999999999992E-5;i:1;d:0.15069511943990579;}s:3:"REP";a:2:{i:0;d:0.0084899499999999996;i:1;d:14.936594819786681;}s:2:"SC";a:2:{i:0;d:3.0599999999999999E-6;i:1;d:0.0053835393787415994;}s:4:"SJCX";a:2:{i:0;d:0.00029600500000000001;i:1;d:0.52076946856353179;}s:5:"STRAT";a:2:{i:0;d:0.00088646500000000002;i:1;d:1.5595814494693374;}s:3:"SYS";a:2:{i:0;d:4.2224999999999996E-5;i:1;d:0.074287565446850987;}s:3:"XMR";a:2:{i:0;d:0.016059965000000002;i:1;d:28.254723528990802;}}'
        ];
    }

    protected function changeRates()
    {

        $result = collect();

        $diffItem = collect();
        $diffItem->put('currency', 'STRAT');
        $diffItem->put('diffBtc', '21');
        $diffItem->put('diffFiat', '20');

        $diffItem = collect();
        $diffItem->put('currency', 'MAID');
        $diffItem->put('diffBtc', '-11');
        $diffItem->put('diffFiat', '-12');

        $diffItem = collect();
        $diffItem->put('currency', 'ETH');
        $diffItem->put('diffBtc', '-11');
        $diffItem->put('diffFiat', '-9');

        $diffItem = collect();
        $diffItem->put('currency', 'SC');
        $diffItem->put('diffBtc', '2');
        $diffItem->put('diffFiat', '3');

        $diffItem = collect();
        $diffItem->put('currency', 'SYS');
        $diffItem->put('diffBtc', '14');
        $diffItem->put('diffFiat', '15');

        $result->push($diffItem);

        return $result;

    }
    /**
     * @test
     */
    public function analyseRateChanges()
    {

        DB::table('rates')->truncate();
        DB::table('rates')->insert($this->rate1());
        DB::table('rates')->insert($this->rate2());

        $rateService = App::make(RateService::class);
        $result = $rateService->analyseRateChanges();

    }

    /**
     * @test
     */
    public function rateChangeAlert()
    {
        $rateService = App::make(RateService::class);
        $rateService->ratechangealert();


    }
}
