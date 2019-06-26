<?php

use Illuminate\Http\Request;

// Ota Routes
Route::group([
    'prefix' => 'gateways',
    'middleware' => \App\Http\Middleware\CheckSecret::class
], function () {
    Route::namespace('Gateways')->group(function () {
        Route::get('list', 'BaseGateWayController@list');
        Route::post('init', 'BaseGateWayController@initTxn');
        Route::post('all', 'BaseGateWayController@all');
        Route::post('update_gateway', 'BaseGateWayController@update_gateway');
        Route::post('update_invoice', 'BaseGateWayController@update_invoice');
    });
});


