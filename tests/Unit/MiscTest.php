<?php
namespace Tests\Unit;

use \Carbon\Carbon;

class MiscTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function date() {


        date_default_timezone_set(config('format.timezone'));
        $now = new Carbon();
        echo $now->format(config('format.datetime'));

    }



}
