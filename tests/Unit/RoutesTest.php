<?php
namespace Tests\Unit;

class RoutesTest extends \Tests\TestCase
{
    /**
     * @test
     */
    public function api_routes_can_be_called() {
        $response = $this->call('GET', 'api/portfolio');
        $this->assertEquals(200, $response->getStatusCode());
        $response = $this->call('GET', 'api/trade_history');
        $this->assertEquals(200, $response->getStatusCode());
        

    }

}
