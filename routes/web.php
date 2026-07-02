<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Google Auth
// Route::get('/google/auth/login', [App\Http\Controllers\Google\AuthController::class, 'login'])->name('google.auth.login');

Route::group([
    'middleware' => ['auth:sanctum']
], function () {

    // User Registration
    Route::get('registration/step/info', function () {
        return view('welcome');
    })->name('user.registration.info');
    
    // Google Calendar
    Route::get('/google/calendar/auth', [App\Http\Controllers\Google\CalendarController::class, 'create']);
    Route::get('/google/calendar/redirect', [App\Http\Controllers\Google\CalendarController::class, 'store'])->name('google.calendar.redirect');

    // Google Contact
    Route::get('/google/contact/auth', [App\Http\Controllers\Google\ContactController::class, 'create']);
    Route::get('/google/contact/redirect', [App\Http\Controllers\Google\ContactController::class, 'store'])->name('google.contact.redirect');

    // Google Drive
    Route::get('/google/drive/auth', [App\Http\Controllers\Google\DriveController::class, 'create']);
    Route::get('/google/drive/redirect', [App\Http\Controllers\Google\DriveController::class, 'store'])->name('google.drive.redirect');
    Route::delete('/google/drive/{account}', [App\Http\Controllers\Google\DriveController::class, 'destroy']);

    Route::group([
        'prefix' => "project/{project}/", 
        'as' => 'project.'
    ], function () {
            
        // Docusign
        Route::get('docusign/connect', [App\Http\Controllers\Project\DocusignController::class, 'connect'])->name("docusign.connect");
        Route::get('docusign/callback', [App\Http\Controllers\Project\DocusignController::class, 'callback'])->name("docusign.callback");

        // Prospect index
        Route::get('prospect', function () {
            return view('welcome');
        })->name('prospect.index');
        
        // Prospect show
        Route::get('prospect/{prospect}', function () {
            return view('welcome');
        })->name('prospect.show');
        
        // User show
        Route::get('user/{user}', function () {
            return view('welcome');
        })->name('user.show');
        
    });

});

Route::get('{any}', function () {
    return view('welcome');
})->where('any', '.*');

Auth::routes();