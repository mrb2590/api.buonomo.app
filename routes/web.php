<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Index
Route::get('/', 'HomeController@index')->name('index');

// Laravel auth routes
Auth::routes(['verify' => true]);

// Routes require authentication
Route::middleware(['auth', 'verified'])->group(function() {

    // Home
    Route::get('/home', 'HomeController@home')->name('home');

});
