<?php

use Illuminate\Support\Facades\Route;

/*
| Minimal legacy API when LEGACY_PUBLIC_API_ENABLED=false.
| Keeps /api/v1/config for app version checks and maintenance/update popups.
| All other /api/v1 routes stay unregistered (no orders, browse, auth, etc.).
*/

Route::group(['namespace' => 'Api\V1', 'middleware' => ['localization', 'react']], function () {
    Route::group(['prefix' => 'config'], function () {
        Route::get('/', 'ConfigController@configuration');
    });
});
