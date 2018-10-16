<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Routes require authentication
Route::middleware(['auth:api'])->prefix('v1')->group(function() {

    /* Users */

    // Fetch current user
    Route::get('/user', 'UserController@fetchCurrent')->name('user.fetch');

    // Fetch
    Route::get('/users/{user?}', 'UserController@fetch')->name('users.fetch');

});
