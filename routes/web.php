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


Route::get('/', 'Controller@getMain')->name('home');
Route::get('/main', 'Controller@getMain')->name('main');
Route::get('/home', 'Controller@getMain')->name('main');


Auth::routes();
