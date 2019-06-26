<?php

use Illuminate\Http\Request;


Route::group([
    'prefix' => 'documentation',
    'middleware' => \App\Http\Middleware\CheckToken::class
], function () {
    Route::post('list', 'Documentation@getDocumentations');
    Route::post('details', 'Documentation@getDocumentationsDetails');
});

