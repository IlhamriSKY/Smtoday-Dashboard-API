<?php

Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::get('profile/activity', 'UserActivityController@show')->name('profile.activity');

    Route::get('activity', 'ActivityController@index')->name('activity.index')
        ->middleware('permission:users.activity');

    Route::get('activity/user/{user}/log', 'UserActivityController@index')->name('activity.user')
        ->middleware('permission:users.activity');
});
