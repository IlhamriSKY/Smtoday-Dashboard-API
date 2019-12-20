<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth'], function () {
    Route::apiResource('/announcements', 'AnnouncementsController')
        ->names('announcements.api');

    Route::post('announcements/read', 'ReadAnnouncementsController@index');
});
