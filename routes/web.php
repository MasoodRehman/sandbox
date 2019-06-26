<?php
ini_set("display_errors", 1);
use App\Libraries\TBO\TBOSOAPClient;


Route::get('tbo', function () {
    $client = new TBOSOAPClient();
    $tbo = $client->createClient();

    // Fetch Dubai hotels (1 Room – 1 Adult)
    $CheckInDate = new DateTime(date('Y-m-d', strtotime(' +2 day')));
    $CheckOutDate = new DateTime(date('Y-m-d', strtotime(' +3 day')));
    $CountryName = "";
    $CityName = "";
    $CityId = 25921;
    $IsNearBySearchAllowed = true;
    $NoOfRooms = 1;
    $GuestNationality = "PK";
    $RoomGuests = array(
        new RoomGuest(2, 0)
    );
    $PreferredCurrencyCode = "PKR";
    $ResultCount = 100;

    $HotelSearchRequest = new HotelSearchRequest($CheckInDate, $CheckOutDate, $CountryName, $CityName, $CityId, $IsNearBySearchAllowed, $NoOfRooms, $GuestNationality, $RoomGuests, $PreferredCurrencyCode, $ResultCount, null);
    $searchResponse = $tbo->HotelSearch($HotelSearchRequest);
    dd($searchResponse);
});

Route::post('/xcrud_ajax_controller', function () {
    include_once app_path('Libraries/xcrud_ajax.php');
});

Route::get('/', 'HomeController@load_home');
Route::get('ajubia_', 'AdminController@login');
Route::post('check_login', 'AdminController@check_login');
Route::get('test_email', 'AdminController@test_email');
Route::get('test_email_send', 'AdminController@test_email_send');

Route::group([
    'prefix' => 'admin',
    'middleware' => \App\Http\Middleware\CheckAdminLogin::class
], function () {

    Route::get('/', 'AdminController@index');
    Route::get('dashboard', 'AdminController@dashboard');
    Route::get('packages', 'AdminController@packages');
    Route::get('modules', 'AdminController@modules');
    Route::post('update/ota_modules', 'AdminController@update_module_supplier');
    Route::get('languages', 'AdminController@languages');
    Route::get('currencies', 'AdminController@currencies');
    Route::get('amenities', 'AmenitiesController@amenities');
    Route::get('meta', 'AdminController@meta_pages');
    Route::get('room/types', 'AdminController@rooms_type');
    Route::get('hotels', 'AdminController@hotels');
    Route::get('accounts', 'AdminController@accounts');
    Route::get('bookings', 'AdminController@bookings');
    Route::get('domains', 'AdminController@domains');
    Route::get('languages', 'AdminController@languages');
    Route::get('suppliers', 'AdminController@suppliers');
    Route::get('transactions', 'AdminController@transactions');
    Route::get('locations', 'AdminController@locations');
    Route::get('social', 'AdminController@social');
    Route::get('countries', 'AdminController@countries');
    Route::get('states', 'AdminController@states');
    Route::get('newsletter', 'AdminController@newsletter');
    Route::get('ota_newsletter', 'AdminController@ota_newsletter');
    Route::get('documentation_categories', 'AdminController@documentation_categories');
    Route::get('documentation', 'AdminController@documentation');
    Route::get('cities', 'AdminController@cities');
    Route::get('show/module', 'AdminController@show');
    Route::get('gateways', 'AdminController@gateways');
    Route::group([
        'prefix' => 'accounts'
    ], function(){
           Route::get('ota', 'AccountController@ota_account');
           Route::get('vendors','AccountController@vendors');
           Route::get('customers','AccountController@customers');
           Route::get('guest_customers','AccountController@guest_customers');
  });

});

