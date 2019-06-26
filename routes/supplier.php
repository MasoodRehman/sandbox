<?php

use Illuminate\Http\Request;

Route::group(['prefix' => 'supplier',
    'middleware' => \App\Http\Middleware\SupplierToken::class
], function () {
    Route::post('subscribe', 'SupplierController@Supplier_Subscribe');
    Route::post('domain', 'SupplierController@add_domain');
    Route::post('get_domain', 'SupplierController@get_domain');
    Route::get('rooms/{id}/gallery', 'RoomsController@gallery');
    Route::post('room/details', 'RoomsController@getRoomDetails');
    Route::get('rooms', 'HotelController@rooms');
    Route::get('hotel_amenities', 'HotelController@hotel_amenities');
    Route::get('rooms_amenities', 'HotelController@rooms_amenities');
    Route::get('hotelGalleryCount/{id}', 'HotelController@galleryCount');
    Route::get('roomGalleryCount/{id}', 'RoomsController@galleryCount');
    Route::get('packageGalleryCount/{id}', 'PackagesController@galleryCount');
    Route::post('social/list', 'OtaController@getSocial');
    Route::post('gallery/save', 'HotelGalleryController@save');
    Route::post('social/update', 'OtaController@update_social');
    Route::post('gallery/deleteMultiple', 'HotelGalleryController@deleteMultiple');
    Route::post('gallery/updateFlagActive', 'HotelGalleryController@updateFlagActive');
    Route::post('gallery/updateFlagThumbnail', 'HotelGalleryCon       troller@updateFlagThumbnail');
    Route::post('gallery/updateSortOrder', 'HotelGalleryController@updateSortOrder');
    Route::post('profile', 'SupplierController@profile');
    Route::post('profile/update', 'SupplierController@update_profile');
    Route::get('locations', 'SupplierController@package_location');
    Route::group(['prefix' => 'packages'], function () {
        Route::get('amenities', 'PackagesController@amenities');
        Route::post('save', 'PackagesController@save');
        Route::post('show', 'PackagesController@show');
        Route::post('update/{uuid}', 'PackagesController@update');
        Route::post('delete_tour_images', 'PackagesController@delete_tour_images');
        Route::get('{id}/gallery', 'PackagesController@gallery');
    });
    Route::group(['prefix' => 'hotels'], function () {
        Route::post('rooms/availability', 'RoomsController@add_room_avilibilty');
        Route::post('rooms/pricing', 'RoomsController@add_room_pricing');
        Route::post('rooms/check/availability', 'RoomsController@getRoomsHotelAvailability');
        Route::post('rooms/check/pricing', 'RoomsController@getRoomsHotelPricing');
        Route::get('{id}/gallery', 'HotelController@gallery');
        Route::post('bookings/all', 'HotelController@getAllBookings');
        Route::post('bookings/getAllMobileCalendarBookings', 'HotelController@getAllMobileCalendarBookings');
        Route::get('/', 'HotelController@hotels');

    });
});