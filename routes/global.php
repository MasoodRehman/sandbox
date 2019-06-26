<?php

use Illuminate\Http\Request;

// Ota Routes
Route::group([
    'prefix' => 'global',
    'middleware' => \App\Http\Middleware\CheckToken::class
], function () {
    Route::post('checkemail', 'GlobalController@checkEmail');
    Route::post('signup', 'GlobalController@signup');
    Route::post('login', 'GlobalController@login');
    Route::post('getSettings', 'GlobalController@getSettings');
    Route::get('countries', 'GlobalController@getCountries');
    Route::get('cities', 'GlobalController@getCities');
    Route::get('states', 'GlobalController@getStates');
    Route::get('locations', 'GlobalController@locations');
    Route::get('packages', 'GlobalController@getPackages');
    Route::get('verify', 'GlobalController@verify');
    Route::get('checkdns', 'GlobalController@checkDNS');
    Route::get('subscribe', 'GlobalController@subscribe');
    Route::get('addCurrencies', 'GlobalController@addCurrencies');
    Route::get('bills/send', 'OtaWalletController@sendBills');
    Route::get('airports', 'GlobalController@airports');
    Route::get('currencies', 'GlobalController@currencies');
    Route::get('bindCities', 'GlobalController@bindCities');
    Route::post('resetpasswrod', 'GlobalController@resetpasswrod');
    Route::get('airlines', 'GlobalController@airlines');
    Route::post('store_fcm', 'GlobalController@store_fcm');
    Route::post('check/ip', 'GlobalController@checkIpAddress');
});