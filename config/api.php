<?php
return  [
    'active' =>  env('API.ACTIVE'),

    'fiat' => env('API.FIAT_CURRENCY', 'EUR'),
    'fiatSymbol' => env('API.FIAT_CURRENCY_SYMBOL', '€'),
    'watchList' => env('API.WATCHLIST', []),
    'dateCorrections' => env('DATE_CORRECTIONS'),
    'adapters' => [
        'poloniex' => [
            'key' => env('API.POLONIEX.KEY'),
            'secret' => env('API.POLONIEX.SECRET'),
            'adapterClass' => App\Adapter\Poloniex\PoloniexAdapter::class
        ],
        'bitcoinde' => [
            'key' => env('API.BITCOINDE.KEY'),
            'secret' => env('API.BITCOINDE.SECRET'),
            'adapterClass' => App\Adapter\Bitcoinde\BitcoindeAdapter::class
        ],
        'bittrex' => [
            'key' => env('API.BITTREX.KEY'),
            'secret' => env('API.BITTREX.SECRET'),
            'adapterClass' => App\Adapter\Bittrex\BittrexAdapter::class
        ],
        'bitfinex' => [
            'key' => env('API.BITFINEX.KEY'),
            'secret' => env('API.BITFINEX.SECRET'),
            'adapterClass' => App\Adapter\Bitfinex\Bitfinexdapter::class
        ],
    ],
    'alertChangeRate' => env('ALERT_CHANGE_RATE') ?? 10,
    'mail' => [
        'alert' =>[
            'subject' => env('MAIL_ALERT_SUBJECT'),
            'receiver' => [
                'adress' => env('MAIL_ALERT_RECEIVER_ADRESS')
            ],
            'sender' => [
                'name' => env('MAIL_ALERT_SENDER_NAME'),
                'adress' => env('MAIL_ALERT_SENDER_ADRESS')
            ]
        ]
    ]
];
