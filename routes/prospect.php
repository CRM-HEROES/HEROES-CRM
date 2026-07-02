<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:prospect']], function () {
    Route::group([
        // 'middleware' => ['checkProspectProject'],
        'prefix' => "project/{project}", 
        'namespace' => "Project",
        'as' => 'project.'
    ], function () {
        Route::get('/', 'HomeController@index')->name('home');
    });
});