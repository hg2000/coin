<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

    Route::get('trade_history/{key?}', 'ApiController@getTradeHistory')->name('api.trade_history.get');
    Route::get('portfolio', 'ApiController@getPortfolio')->name('api.portfolio.get');
    Route::get('refresh', 'ApiController@getRefresh')->name('api.coin.refresh');
    Route::get('lastRefreshDateTime', 'ApiController@getLastRefreshDateTime')->name('api.coin.lastRefreshDateTime');
