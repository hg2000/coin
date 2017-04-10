<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', 'Controller@getHome')->name('home.get');


Route::group(['prefix' => 'api'], function () {
    Route::get('trade_history', 'ApiController@getTradeHistory')->name('api.trade_history.get');
    Route::get('volumes', 'ApiController@getVolumes')->name('api.volumes.get');
});
