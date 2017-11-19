<?php

namespace APP\Adapter\Bittrex;

use \Exception;

/**
 * Bittrex api documentation see: https://bittrex.com/home/api
 */
class Connector
{
    protected $key;
    protected $secret;

    public function __construct($key, $secret) {

        $this->key = $key;
        $this->secret = $secret;
    }

    public function request($method, $arg1 = null, $arg2 = null)
    {
        $uri=$this->getUri($method, $arg1, $arg2);
        $sign=hash_hmac('sha512', $uri, $this->secret);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        $execResult = curl_exec($ch);
        $result =  json_decode($execResult);

        if (!$result->success) {
            $code = 0 ;
            if ($result->message == 'INVALID_MARKET') {
                $code = 891;
            }
            throw new Exception('Bittrex API error: ' . $result->message, $code);
        } else {
            return $result;
        }
    }

    protected function getUri($method, $arg1 = null, $arg2 = null) {
        $nonce = time();
        $base = 'https://bittrex.com/api/v1.1/';
        $key = '?apikey=' . $this->key . '&nonce=' . $nonce ;

        switch ($method) {
            case 'ticker':
                return $base . 'public/getticker' . $key . '&market=' . $arg1 . '-' . $arg2;
            break;
            case 'balances':
                return $base . 'account/getbalances' . $key;
            break;
            case 'history':
                return $base . 'account/getorderhistory' . $key;
            break;
        }
    }
}
