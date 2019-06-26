<?php

use Illuminate\Http\Request;

// Ota Routes
Route::group(['prefix' => 'ota',
    'middleware' => \App\Http\Middleware\CheckSecret::class
], function () {
    Route::post('settings', 'OtaController@settings');
    Route::post('get_ota', 'OtaController@get_ota');
    Route::post('domians', 'OtaController@domians');
    Route::post('logout', 'OtaController@logout');
    Route::post('delete_category', 'BlogController@delete_category');
    Route::get('getsettings', 'OtaController@getSettings');
    Route::post('add_blog', 'BlogController@add_blog');
    Route::post('blog/image', 'BlogController@add_blog_image');
    Route::post('social/list', 'OtaController@getSocial');
    Route::post('social/update', 'OtaController@update_social');
    Route::post('add_category', 'BlogController@add_category');
    Route::post('get_categories', 'BlogController@get_categories');
    Route::post('get_blogs', 'BlogController@get_blogs');
    Route::post('blog/update_settings', 'BlogController@blog_settings');
    Route::post('account/update', 'OtaController@update_account');
    Route::get('blog/get_settings', 'BlogController@get_blog_settings');
    Route::post('delete_blog', 'BlogController@delete_blog');
    Route::post('get_blog_details', 'BlogController@get_blog_details');
    Route::post('account', 'OtaController@getAccounts');
    Route::post('subscribe', 'OtaController@Ota_Subscribe');
    Route::post('customizations_update', 'OtaController@customizations_update');
    Route::post('customizations', 'OtaController@customizations');
    Route::post('dashboard/clients', 'OtaController@get_dashboard_status');


    Route::group(['prefix' => 'modules'], function () {
        Route::post('settings', 'OtaController@get_modules_settings');
        Route::post('getsettings', 'OtaController@modules_settings');
        Route::post('update', 'OtaController@module_update');
        Route::post('update_features', 'OtaController@module_update_features');
        Route::post('update_order', 'OtaController@update_order');
        Route::post('update/settings', 'OtaController@update_modules_setting');
        Route::post('/', 'OtaController@getModules');
        Route::post('suppliers/all', 'ModuleController@get_modules_suppliers');

    });

    Route::group(['prefix' => 'languages'], function () {
        Route::post('get_languages', 'OtaController@getLanguages');
        Route::post('add_language', 'OtaController@addLanguage');
        Route::post('delete_language', 'OtaController@deletelanguage');
        Route::post('change_default', 'OtaController@change_default');
    });

    Route::group(['prefix' => 'wallet'], function () {
        Route::post('update', 'OtaWalletController@update_wallet');
        Route::post('make_invoice', 'OtaWalletController@make_transcation');
    });

    Route::group(['prefix' => 'meta'], function () {
        Route::post('page', 'OtaController@get_meta_tag');
        Route::post('pages', 'OtaController@get_meta_tags');
        Route::post('update/page', 'OtaController@update_meta_page');
    });


    Route::group(['prefix' => 'cms'], function () {
        Route::post('update', 'OtaController@update_cms');
        Route::post('pages', 'OtaController@cms_pages');
        Route::post('page', 'OtaController@cms_page');
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::namespace('Reports')->group(function () {
            Route::post('list', 'OtaReportesController@getOtaReports');
            Route::post('current', 'OtaReportesController@getOtaReports_current');
        });
    });

    Route::group(['prefix' => 'currencies'], function () {
        Route::post('add_currency', 'OtaController@addCurrency');
        Route::post('getCurrencies', 'OtaController@getCurrencies');
        Route::post('delete_currency', 'OtaController@deleteCurrency');
        Route::post('change_default', 'OtaController@change_default_currency');
    });

    Route::group(['prefix' => 'ivisa'], function () {
        Route::post('listing', 'IvisaController@index');
    });

//    Route::group(['prefix' => 'kiwi'], function () {
//        Route::get('searching', 'KiwiController@index');
//        Route::post('booking', 'KiwiController@Booking');
//        Route::post('savebooking', 'KiwiController@SaveBooking');
//        Route::post('invoice', 'KiwiController@invoice');
//        Route::get('voucher', 'KiwiController@voucher');
//        Route::post('validate', 'KiwiController@vildatefromto');
//        Route::post('make_payment', 'KiwiController@make_payment');
//        Route::post('confirm_payment', 'KiwiController@confirm_payment');
//    });

    Route::group(['prefix' => 'flights'], function () {
        Route::get('searching', 'FlightsController@index');
        Route::post('booking', 'FlightsController@Booking');
        Route::post('savebooking', 'FlightsController@SaveBooking');
        Route::post('invoice', 'FlightsController@invoice');
        Route::get('voucher', 'FlightsController@voucher');
        Route::post('validate', 'FlightsController@vildatefromto');
    });

    Route::group(['prefix' => 'hotels'], function () {
        Route::post('search', 'HotelController@getHotels');
        Route::post('filters', 'HotelController@getHotelsFilters');
        Route::post('detail', 'HotelController@getHotelDetails');
        Route::post('get_hotel_by_slug', 'HotelController@getHotelBySlug');
        Route::post('list', 'HotelController@getAllHotels');
        Route::post('room/details', 'HotelController@getRoomDetails');
        Route::post('delete_hotel_images', 'HotelController@delete_hotel_images');
        Route::post('add_image', 'HotelController@thumb_image');
        Route::post('feature_cities', 'HotelController@feature_cities');
        Route::post('delete_feature_cities', 'HotelController@delete_hotel_feature');
        Route::post('features/change_number', 'HotelController@change_number');
        Route::post('bookings', 'HotelController@save_booking');
        Route::post('update_booking', 'HotelController@update_booking');
        Route::post('invoice', 'HotelController@invoice');
        Route::post('update_invoice', 'HotelController@update_invoice');
    });

    Route::group(['prefix' => 'jachotels'], function () {
        Route::get('search', 'JacController@getHotels');
        Route::post('filters', 'HotelController@getHotelsFilters');
        Route::post('detail', 'HotelController@getHotelDetails');
        Route::post('list', 'HotelController@getAllHotels');
        Route::post('room/details', 'HotelController@getRoomDetails');
        Route::post('delete_hotel_images', 'HotelController@delete_hotel_images');
        Route::post('add_image', 'HotelController@thumb_image');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::post('signup', 'OtaUserAccountsController@signup');
        Route::post('verifications', 'OtaUserAccountsController@verifications');
        Route::post('login', 'OtaUserAccountsController@login');
        Route::post('userdata', 'OtaUserAccountsController@userdata');
        Route::post('update', 'OtaUserAccountsController@userupdate');
    });

    Route::group(['prefix' => 'widgets'], function () {
        Route::post('update', 'WidgetsController@update');
        Route::post('list', 'WidgetsController@index');
    });

    Route::group(['prefix' => 'packages'], function () {
        Route::get('locations', 'PackagesController@locations');
        Route::post('search', 'PackagesController@search');
        Route::get('show', 'PackagesController@show');
        Route::get('tour_detail', 'PackagesController@tourDetail');
    });

});

