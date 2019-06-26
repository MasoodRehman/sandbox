<?php

use Illuminate\Http\Request;

require_once "ota.php";
require_once "supplier.php";
require_once "global.php";
require_once "gateways.php";
require_once "documentation.php";


Route::post( 'supplier/signup', 'SupplierController@signup' )->middleware( \App\Http\Middleware\CheckSecret::class );
Route::get( 'supplier/verifications', 'SupplierController@verifications' );
Route::post( 'checkhook', 'OtaWalletController@checkhook' );
Route::post( 'save_booking_test', 'HotelController@save_booking_test' );


Route::group( [
	'prefix'     => 'global/supplier',
	'middleware' => \App\Http\Middleware\CheckToken::class
], function () {
	Route::post( 'login', 'SupplierController@login' );
	Route::post( 'city_name', 'HotelController@city_name' );
	Route::post( 'getSettings', 'SupplierController@getSettings' );
} );


Route::group( [
	'prefix' => 'flight'
], function () {
	Route::namespace( 'Api' )->group( function () {
		Route::get( 'search', 'FlightsApiController@index' )->middleware( \App\Http\Middleware\CheckSecret::class );
        Route::group( [
			'middleware' => \App\Http\Middleware\CheckJsonSecret::class
		], function () {
			Route::post( 'details', 'FlightsApiController@details' );
			Route::post( 'booking', 'FlightsApiController@booking' );
		} );
	} );
} );
Route::namespace( 'Api' )->group( function () {

    Route::post('currency', 'GlobalApiController@currency_convert')->middleware(\App\Http\Middleware\CheckSecret::class);
});
Route::group( [
	'prefix' => 'hotels'
], function () {
	Route::namespace( 'Api' )->group( function () {
        Route::get( 'cities_bind', 'HotelsApiController@cities_bind' );
        Route::get( 'search', 'HotelsApiController@index' )->middleware( \App\Http\Middleware\CheckSecret::class );
		Route::get( 'details', 'HotelsApiController@detail' )->middleware( \App\Http\Middleware\CheckSecret::class );
		Route::group( [
			'middleware' => \App\Http\Middleware\CheckJsonSecret::class
		], function () {
            Route::post('booking', 'HotelsApiController@booking' );

        } );
	} );
} );

// This is global routes in which we use only token.
Route::get( 'addCountry', 'GlobalController@addCountry' );
Route::get( 'hotels/locations', 'HotelController@location' )->middleware( \App\Http\Middleware\CheckToken::class );;
Route::post( 'sitemap/hotels', 'HotelController@getCitiesOfHotels' )->middleware( \App\Http\Middleware\CheckSecret::class );;


Route::get( 'bindCountries', 'Documentation@bind_country' );


