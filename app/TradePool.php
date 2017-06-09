<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Service\Helper;
use App\Trade;

class TradePool extends Model
{

    protected $sellTrade;
    protected $buyTrades;

    protected $allBuyTrades;
    protected $allSellTrades;

    protected $originalSellVolume;

    public function __construct(Trade $sellTrade, $allBuyTrades) {

        $this->sellTrade = $sellTrade;
        $this->allBuyTrades = $allBuyTrades;
        $this->buyTrades = collect();

        $this->originalSellVolume = $sellTrade->volume;
        $transferVolume = 1;
        while ($transferVolume > 0) {
            $buyTrade = $this->allBuyTrades->pop();
            $transferVolume = $buyTrade->takeVolume($this->sellTrade->volume);

            $this->buyTrades->push($buyTrade);
        }
    }

}
