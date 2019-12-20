<?php

Route::get('users/{user}/activity', 'UserActivityController@index');
Route::get('/activity', 'ActivityController@index');
Route::get('/stats/activity', 'StatsController@show');
