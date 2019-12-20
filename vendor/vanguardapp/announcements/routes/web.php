<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| Now create something great!
|
*/

Route::group(['middleware' => ['auth', 'verified']], function () {
    Route::get('announcements/list', 'AnnouncementListController@index')
        ->name('announcements.list');

    Route::post('announcements/read', 'ReadAnnouncementsController@index');

    Route::resource('announcements', 'AnnouncementsController');
});
