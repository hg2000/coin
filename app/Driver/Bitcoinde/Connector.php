<?php

namespace APP\Driver\Bitcoinde;

use \Exception;

class Connector
{
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';

    // Request
    const ERROR_CODE_MISSING_HEADER                           = 1;
    const ERROR_CODE_INACTIVE_API_KEY                         = 2;
    const ERROR_CODE_INVALID_API_KEY                          = 3;
    const ERROR_CODE_INVALID_NONCE                            = 4;
    const ERROR_CODE_INVALID_SIGNATURE                        = 5;
    const ERROR_CODE_INSUFFICIENT_CREDITS                     = 6;
    const ERROR_CODE_INVALID_ROUTE                            = 7;
    const ERROR_CODE_UNKOWN_API_ACTION                        = 8;
    const ERROR_CODE_ADDITIONAL_AGREEMENT_NOT_ACCEPTED        = 9;
    const ERROR_CODE_API_KEY_BANNED                           = 32;
    const ERROR_CODE_IP_BANNED                                = 33;

    const ERROR_CODE_NO_KYC_FULL                              = 44;
    const ERROR_CODE_NO_2_FACTOR_AUTHENTICATION               = 10;
    const ERROR_CODE_NO_BETA_GROUP_USER                       = 11;
    const ERROR_CODE_TECHNICAL_REASON                         = 12;
    const ERROR_CODE_TRADING_API_CURRENTLY_UNAVAILABLE        = 13;
    const ERROR_CODE_NO_ACTION_PERMISSION_FOR_API_KEY         = 14;
    const ERROR_CODE_MISSING_POST_PARAMETER                   = 15;
    const ERROR_CODE_MISSING_GET_PARAMETER                    = 16;
    const ERROR_CODE_INVALID_NUMBER                           = 17;
    const ERROR_CODE_NUMBER_TOO_LOW                           = 18;
    const ERROR_CODE_NUMBER_TOO_BIG                           = 19;
    const ERROR_CODE_TOO_MANY_DECIMAL_PLACES                  = 20;
    const ERROR_CODE_INVALID_BOOLEAN_VALUE                    = 21;
    const ERROR_CODE_FORBIDDEN_PARAMETER_VALUE                = 22;
    const ERROR_CODE_INVALID_MIN_AMOUNT                       = 23;
    const ERROR_CODE_INVALID_DATETIME_FORMAT                  = 24;
    const ERROR_CODE_DATE_LOWER_THAN_MIN_DATE                 = 25;
    const ERROR_CODE_INVALID_VALUE                            = 26;
    const ERROR_CODE_FORBIDDEN_VALUE_FOR_GET_PARAMETER        = 27;
    const ERROR_CODE_FORBIDDEN_VALUE_FOR_POST_PARAMETER       = 28;
    const ERROR_CODE_EXPRESS_TRADE_TEMPORARILY_NOT_AVAILABLE  = 29;
    const ERROR_CODE_END_DATETIME_YOUNGER_THAN_START_DATETIME = 30;
    const ERROR_CODE_PAGE_GREATER_THAN_LAST_PAGE              = 31;

    // Order
    const ERROR_CODE_ORDER_NOT_FOUND                          = 50;
    const ERROR_CODE_ORDER_NOT_POSSIBLE                       = 51;
    const ERROR_CODE_INVALID_ORDER_TYPE                       = 52;
    const ERROR_CODE_PAYMENT_OPTION_NOT_ALLOWED_FOR_TYPE_BUY  = 53;
    const ERROR_CODE_CANCELLATION_NOT_ALLOWED                 = 54;
    const ERROR_CODE_TRADING_SUSPENDED                        = 55;
    const ERROR_CODE_EXPRESS_TRADE_NOT_POSSIBLE               = 56;
    const ERROR_CODE_NO_BANK_ACCOUNT                          = 57;

    // Trade
    const ERROR_CODE_NO_ACTIVE_RESERVATION                    = 70;
    const ERROR_CODE_EXPRESS_TRADE_NOT_ALLOWED                = 71;
    const ERROR_CODE_EXPRESS_TRADE_FAILURE_TEMPORARY          = 72;
    const ERROR_CODE_EXPRESS_TRADE_FAILURE                    = 73;
    const ERROR_CODE_INVALID_TRADE_STATE                      = 74;
    const ERROR_CODE_TRADE_NOT_FOUND                          = 75;
    const ERROR_CODE_RESERVATION_AMOUNT_INSUFFICIENT          = 76;

    const METHOD_SHOW_ORDERBOOK         = 'showOrderbook';
    const METHOD_CREATE_ORDER           = 'createOrder';
    const METHOD_DELETE_ORDER           = 'deleteOrder';
    const METHOD_SHOW_MY_ORDERS         = 'showMyOrders';
    const METHOD_SHOW_MY_ORDER_DETAILS  = 'showMyOrderDetails';
    const METHOD_EXECUTE_TRADE          = 'executeTrade';
    const METHOD_SHOW_MY_TRADES         = 'showMyTrades';
    const METHOD_SHOW_MY_TRADE_DETAILS  = 'showMyTradeDetails';
    const METHOD_SHOW_ACCOUNT_INFO      = 'showAccountInfo';
    const METHOD_SHOW_ACCOUNT_LEDGER    = 'showAccountLedger';

    // LEGACY API-METHODS
    const METHOD_SHOW_PUBLIC_TRADE_HISTORY = 'showPublicTradeHistory';
    const METHOD_SHOW_ORDERBOOK_COMPACT    = 'showOrderbookCompact';
    const METHOD_SHOW_RATES                = 'showRates';

    const HEADER_X_NONCE         = 'X-API-NONCE';
    const HEADER_X_API_KEY       = 'X-API-KEY';
    const HEADER_X_API_SIGNATURE = 'X-API-SIGNATURE';

    const ORDER_TYPE_BUY  = 'buy';
    const ORDER_TYPE_SELL = 'sell';

    // Mandatory parameters for searching the orderbook
    const SHOW_ORDERBOOK_PARAMETER_TYPE   = 'type'; // string (buy|sell)
    const SHOW_ORDERBOOK_PARAMETER_AMOUNT = 'amount'; // float
    const SHOW_ORDERBOOK_PARAMETER_PRICE  = 'price'; // float

    // Optional parameters for searching the orderbook
    const SHOW_ORDERBOOK_PARAMETER_ORDER_REQUIREMENTS_FULLFILLED = 'order_requirements_fullfilled'; // boolean (default: false)
    const SHOW_ORDERBOOK_PARAMETER_ONLY_KYC_FULL                 = 'only_kyc_full'; // boolean (default: false)
    const SHOW_ORDERBOOK_PARAMETER_ONLY_EXPRESS_ORDERS           = 'only_express_orders'; // boolean (default: false)
    const SHOW_ORDERBOOK_PARAMETER_ONLY_SAME_BANKGROUP           = 'only_same_bankgroup'; // boolean (default: false)
    const SHOW_ORDERBOOK_PARAMETER_ONLY_SAME_BIC                 = 'only_same_bic'; // boolean (default: false)
    const SHOW_ORDERBOOK_PARAMETER_SEAT_OF_BANK                  = 'seat_of_bank'; // array (default: all possible countries)

    // Mandatory parameters for create new order
    const CREATE_ORDER_PARAMETER_TYPE           = 'type'; // string (buy|sell)
    const CREATE_ORDER_PARAMETER_MAX_AMOUNT     = 'max_amount'; // float
    const CREATE_ORDER_PARAMETER_PRICE          = 'price'; // float

    // Optional parameters for create new order
    const CREATE_ORDER_PARAMETER_MIN_AMOUNT                     = 'min_amount'; // float (default: max_amount/2)
    const CREATE_ORDER_PARAMETER_END_DATETIME                   = 'end_datetime'; // string (format: RFC 3339, default: current date + 5 days)
    const CREATE_ORDER_PARAMETER_NEW_ORDER_FOR_REMAINING_AMOUNT = 'new_order_for_remaining_amount'; // boolean ( default: false)
    const CREATE_ORDER_PARAMETER_MIN_TRUST_LEVEL                = 'min_trust_level'; // string (bronze|silver|gold, default: default setting user account)
    const CREATE_ORDER_PARAMETER_ONLY_KYC_FULL                  = 'only_kyc_full'; // boolean (default: false)
    const CREATE_ORDER_PARAMETER_PAYMENT_OPTION                 = 'payment_option'; // integer (1|2|3)
    const CREATE_ORDER_PARAMETER_SEAT_OF_BANK                   = 'seat_of_bank'; // array (default: all possible countries)

    // Mandatory parameters for delete order
    const DELETE_ORDER_PARAMETER_ORDER_ID  = 'order_id'; // string

    // Optional parameters for my orders list
    const SHOW_MY_ORDERS_PARAMETER_TYPE             = 'type'; // string (buy|sell)
    const SHOW_MY_ORDERS_PARAMETER_STATE            = 'state'; // integer (-2, -1, 0 | default: 0)
    const SHOW_MY_ORDERS_PARAMETER_DATE_START       = 'date_start'; // string
    const SHOW_MY_ORDERS_PARAMETER_DATE_END         = 'date_end'; // string
    const SHOW_MY_ORDERS_PARAMETER_PAGE             = 'page'; // string
    const SHOW_MY_ORDERS_PARAMETER_SINCE_ORDER_ID   = 'since_order_id'; // string

    // Mandatory parameters for my order details
    const SHOW_MY_ORDER_DETAILS_PARAMETER_ORDER_ID  = 'order_id'; // string

    // Mandatory parameters for execute trade
    const EXECUTE_TRADE_PARAMETER_TYPE     = 'type'; // string (buy|sell)
    const EXECUTE_TRADE_PARAMETER_ORDER_ID = 'order_id'; // string
    const EXECUTE_TRADE_PARAMETER_AMOUNT   = 'amount'; // string

    // Optional parameters for my trade list
    const SHOW_MY_TRADES_PARAMETER_TYPE             = 'type'; // string (buy|sell)
    const SHOW_MY_TRADES_PARAMETER_STATE            = 'state'; // integer (-2, -1, 0 | default: 0)
    const SHOW_MY_TRADES_PARAMETER_DATE_START       = 'date_start'; // string
    const SHOW_MY_TRADES_PARAMETER_DATE_END         = 'date_end'; // string
    const SHOW_MY_TRADES_PARAMETER_PAGE             = 'page'; // string
    const SHOW_MY_TRADES_PARAMETER_SINCE_TRADE_ID   = 'since_trade_id'; // string

    // Mandatory parameters for my trade details
    const SHOW_MY_TRADE_DETAILS_PARAMETER_TRADE_ID  = 'trade_id'; // string

    // Optional parameters for public trade history
    const SHOW_PUBLIC_TRADE_HISTORY_PARAMETER_SINCE_TID = 'since_tid'; // integer

    // Optional parameters for show account statement
    const SHOW_ACCOUNT_LEDGER_PARAMETER_TYPE           = 'type'; // string
    const SHOW_ACCOUNT_LEDGER_PARAMETER_DATETIME_START = 'datetime_start'; // string
    const SHOW_ACCOUNT_LEDGER_PARAMETER_DATETIME_END   = 'datetime_end'; // string
    const SHOW_ACCOUNT_LEDGER_PARAMETER_PAGE           = 'page'; // string

    const ORDER_STATE_EXPIRED   = -2;
    const ORDER_STATE_CANCELLED = -1;
    const ORDER_STATE_PENDING   = 0;

    const TRADE_STATE_CANCELLED  = -1;
    const TRADE_STATE_PENDING    = 0;
    const TRADE_STATE_SUCCESSFUL = 1;

    const TRADE_PAYMENT_METHOD_SEPA    = 1;
    const TRADE_PAYMENT_METHOD_EXPRESS = 2;

    const ORDER_PAYMENT_OPTION_ONLY_EXPRESS   = 1;
    const ORDER_PAYMENT_OPION_ONLY_SEPA       = 2;
    const ORDER_PAYMENT_OPION_EXPRESS_OR_SEPA = 3;

    const MIN_TRUST_LEVEL_BRONZE = 'bronze';
    const MIN_TRUST_LEVEL_SILVER = 'silver';
    const MIN_TRUST_LEVEL_GOLD   = 'gold';

    const RATING_PENDING  = 'pending';
    const RATING_NEGATIVE = 'negative';
    const RATING_NEUTRAL  = 'neutral';
    const RATING_POSITIVE = 'positive';

    const ACCOUNT_LEDGER_TYPE_ALL             = 'all';
    const ACCOUNT_LEDGER_TYPE_BUY             = 'buy';
    const ACCOUNT_LEDGER_TYPE_SELL            = 'sell';
    const ACCOUNT_LEDGER_TYPE_INPAYMENT       = 'inpayment';
    const ACCOUNT_LEDGER_TYPE_PAYOUT          = 'payout';
    const ACCOUNT_LEDGER_TYPE_AFFILIATE       = 'affiliate';
    const ACCOUNT_LEDGER_TYPE_BUY_YUBIKEY     = 'buy_yubikey';
    const ACCOUNT_LEDGER_TYPE_BUY_GOLDSHOP    = 'buy_goldshop';
    const ACCOUNT_LEDGER_TYPE_BUY_DIAMONDSHOP = 'buy_diamondshop';
    const ACCOUNT_LEDGER_TYPE_KICKBACK        = 'kickback';


    protected $api_key;
    protected $secret;
    protected $curl_handle;

    protected $method_settings = array(
        self::METHOD_SHOW_ORDERBOOK => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'orders',
            'parameters'  => array(
                self::SHOW_ORDERBOOK_PARAMETER_TYPE,
            ),
        ),
        self::METHOD_CREATE_ORDER   => array(
            'http_method' => self::HTTP_METHOD_POST,
            'entity'      => 'orders',
            'parameters'  => array(
                self::CREATE_ORDER_PARAMETER_TYPE,
                self::CREATE_ORDER_PARAMETER_PRICE,
                self::CREATE_ORDER_PARAMETER_MAX_AMOUNT,
            ),
        ),
        self::METHOD_DELETE_ORDER   => array(
            'http_method' => self::HTTP_METHOD_DELETE,
            'entity'      => 'orders',
            'id'          => 'order_id',
        ),
        self::METHOD_SHOW_MY_ORDERS => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'orders',
            'subentity'   => 'my_own',
        ),
        self::METHOD_SHOW_MY_ORDER_DETAILS     => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'orders',
            'id'          => 'order_id',
        ),
        self::METHOD_EXECUTE_TRADE          => array(
            'http_method' => self::HTTP_METHOD_POST,
            'entity'      => 'trades',
            'parameters'  => array(
                self::EXECUTE_TRADE_PARAMETER_TYPE,
                self::EXECUTE_TRADE_PARAMETER_AMOUNT,
            ),
            'id'          => self::EXECUTE_TRADE_PARAMETER_ORDER_ID,
        ),
        self::METHOD_SHOW_MY_TRADES    => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'trades',
        ),
        self::METHOD_SHOW_MY_TRADE_DETAILS     => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'trades',
            'id'          => 'trade_id',
        ),
        self::METHOD_SHOW_ACCOUNT_INFO   => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'account',
        ),

        // LEGACY API
        self::METHOD_SHOW_PUBLIC_TRADE_HISTORY    => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'trades',
            'subentity'   => 'history',
        ),
        self::METHOD_SHOW_ORDERBOOK_COMPACT    => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'orders',
            'subentity'   => 'compact',
        ),
        self::METHOD_SHOW_RATES    => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'rates',
        ),
        self::METHOD_SHOW_ACCOUNT_LEDGER   => array(
            'http_method' => self::HTTP_METHOD_GET,
            'entity'      => 'account',
            'subentity'   => 'ledger',
        ),
    );

    protected $options = array(
        'uri'             => 'https://api.bitcoin.de/',
        'verify_ssl_peer' => true,
        'api_version'     => 1,
    );

    /**
     * Constructor
     *
     * @access public
     *
     * @param string $api_key API-Key
     * @param string $secret  API-Secret
     * @param array  $options Options
     */
    public function __construct($api_key, $secret, array $options = array())
    {
        $this->api_key = $api_key;
        $this->secret  = $secret;

        $this->options     = array_replace($this->options, $options);
        $this->curl_handle = curl_init();

        curl_setopt_array($this->curl_handle, array(
            CURLOPT_SSL_VERIFYPEER => $this->options['verify_ssl_peer'],
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Bitcoin.de Trading-API',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
        ));
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
        curl_close($this->curl_handle);
    }

    /**
     * Executes an api-request
     *
     * The returning array contains the following items:
     *  - successful (request has succeeded)
     *  - errors (an array of errors, which only contains errors, if "successful" is FALSE)
     *  - credits (remaining credits)
     *  - headers (array of response-headers)
     *  - status-code (http-status-code)
     *  - maintenance (just in case if any planned maintenance is coming up)
     * and any api-method specific response-data
     *
     * @access public
     * @param  string $api_method  API-Method
     * @param  array  $parameters  GET-/POST-Parameters
     *
     * @return array
     *
     * @throws Exception if the request failed
     */
    public function doRequest($api_method, array $parameters = array())
    {

        // Check if the method exists
        if (false === isset($this->method_settings[$api_method])) {
            throw new Exception('API-Method >>' . $api_method . '<< doesn´t exists');
        }

        // Are all mandatory parameters given?
        if (true === isset($this->method_settings[$api_method]['parameters'])) {
            foreach ($this->method_settings[$api_method]['parameters'] as $mandatory_parameter) {
                if (false === isset($parameters[$mandatory_parameter])) {
                    throw new Exception('Value for mandatory '.$this->method_settings[$api_method]['http_method'].'-parameter "'.$mandatory_parameter.'" is missing');
                }
            }
        }

        if (true === isset($this->method_settings[$api_method]['id'])
            && false === isset($parameters[$this->method_settings[$api_method]['id']])) {
            throw new Exception('Value for mandatory GET-parameter '.$this->method_settings[$api_method]['id'].' is missing');
        }

        // Prepare the nonce
            $nonce = explode(' ', microtime());
            $nonce = $nonce[1] . str_pad(substr($nonce[0], 2, 6), 6, '0');

            $id = '';
            $subentity = '';

        if (true === isset($this->method_settings[$api_method]['id'])) {
            if (true === isset($parameters[$this->method_settings[$api_method]['id']])) {
                $id = '/' . $parameters[$this->method_settings[$api_method]['id']];
                unset($parameters[$this->method_settings[$api_method]['id']]);
            }

        }
        if (true === isset($this->method_settings[$api_method]['subentity'])) {
            $subentity = '/'.$this->method_settings[$api_method]['subentity'];
        }

            $post_parameters = (self::HTTP_METHOD_POST === $this->method_settings[$api_method]['http_method']) ? $parameters : array();
            $post_parameters = ksort_recursive($post_parameters);

            $prepared_post_parameters = (0 < count($post_parameters)) ? (http_build_query($post_parameters, '', '&')) : '';
            $prepared_post_parameters_hash = md5($prepared_post_parameters);

            $get_parameters = (self::HTTP_METHOD_GET === $this->method_settings[$api_method]['http_method']) ? $parameters : array();
            $prepared_get_parameters = (0 < count($get_parameters)) ? ('?'.http_build_query($get_parameters, '', '&')) : '';

            $http_method = $this->method_settings[$api_method]['http_method'];
            $uri         = $this->options['uri'].'v'.$this->options['api_version'].'/'.$this->method_settings[$api_method]['entity'].$id.$subentity.$prepared_get_parameters;
            $request_headers = array();
            $request_headers[self::HEADER_X_API_KEY] = $this->api_key;
            $request_headers[self::HEADER_X_NONCE] = $nonce;

            $hmac_data = implode('#', array($http_method, $uri, $this->api_key, $nonce, $prepared_post_parameters_hash));
            $s_hmac = hash_hmac(
                'sha256',
                $hmac_data,
                $this->secret
            );
            $request_headers[self::HEADER_X_API_SIGNATURE] = $s_hmac;

            foreach ($request_headers as $s_name => &$m_value) {
                $m_value = $s_name . ': ' . $m_value;
            }

            curl_setopt($this->curl_handle, CURLOPT_URL, $uri);
            curl_setopt($this->curl_handle, CURLOPT_CUSTOMREQUEST, $http_method);
            curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $request_headers);
            curl_setopt($this->curl_handle, CURLOPT_FORBID_REUSE, true);

            if (0 < count($post_parameters)) {
                curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $prepared_post_parameters);
            }



            $result = curl_exec($this->curl_handle);

            if (false === $result) {
                throw new Exception('CURL error: ' . curl_error($this->curl_handle));
            }

            $curl_info         = curl_getinfo($this->curl_handle);
            $curl_error        = curl_error($this->curl_handle);
            $curl_error_number = curl_errno($this->curl_handle);
            $http_code         = $curl_info['http_code'];
            $header_size       = $curl_info['header_size'];

            $response_headers = substr($result, 0, $header_size);
            $body   = substr($result, $header_size);

            $prepared_reponse_headers = self::parseHttpHeaders($response_headers);

            $result = json_decode($body, true);
            $json_last_error = json_last_error();


            if (JSON_ERROR_NONE !== $json_last_error) {
                throw new Exception(json_last_error_msg()."\n\n".$body);
            }

            if (true === is_array($result)) {
                $result['successful']  = (200 === $http_code || 201 === $http_code);
                $result['headers']     = $prepared_reponse_headers;
                $result['status_code'] = $http_code;
            }

            if ($result['errors']) {
                throw new \Exception(json_encode($result['errors']));
            }
            return $result;
    }

    /**
     * Helper method for obtaining the response-headers
     *
     * @static
     * @access public
     * @param  string $header Headers in type of a string
     *
     * @return array
     */
    public static function parseHttpHeaders($header)
    {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));

        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./', function ($matches) {
                    return strtoupper($matches[0]);

                }, strtolower(trim($match[1])));
                if (isset($retVal[$match[1]])) {
                    if (!is_array($retVal[$match[1]])) {
                        $retVal[$match[1]] = array($retVal[$match[1]]);
                    }
                    $retVal[$match[1]][] = $match[2];
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }

        return $retVal;
    }
}

if (!function_exists('json_last_error_msg')) {
    function json_last_error_msg()
    {
        $number = json_last_error();

        switch ($number) {
            case JSON_ERROR_NONE:
                return ' - No errors';
            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';
            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return ' - Unknown error';
        }
    }
}

/**
 * Recursively sorts an array by it´s keys
 *
 * @param  array  $values  Array to sort
 *
 * @return array
 */
function ksort_recursive(array $values = array())
{
    foreach ($values as $index => $value) {
        if (true === is_array($value)) {
            $value = ksort_recursive($value);
            $values[$index] = $value;
        }
    }

    ksort($values);

    return $values;
}

function strtoupper_match(array $matches)
{
    return strtoupper($matches[0]);
}
