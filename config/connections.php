<?php
return [
    'poloniex' => [
        'key' => env('API.POLONIEX.KEY'),
        'secret' => env('API.POLONIEX.SECRET'),
        'driverClass' => App\Driver\Poloniex\PoloniexDriver::class
    ],
    'bitcoinde' => [
        'key' => env('API.BITCOINDE.KEY'),
        'secret' => env('API.BITCOINDE.SECRET'),
        'driverClass' => App\Driver\Bitcoinde\BitcoindeDriver::class
    ],
];
