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
Route::middleware(['auth:api', 'verified'])->prefix('v1')->group(function() {

    /* Users */

    // Fetch current user
    Route::get('/user', 'UserController@fetchCurrent')->name('user.fetch');

    // Fetch all or a single user
    Route::get('/users/{user?}', 'UserController@fetch')->name('users.fetch');

    // Create a user
    Route::post('/users', 'UserController@store')->name('users.create');

    // Update a user
    Route::patch('/users/{user}', 'UserController@update')->name('users.update');

    // Trash a user
    Route::delete('/users/{user}/trash', 'UserController@trash')->name('users.trash');

    // Delete a trashed user
    Route::delete('/users/{trashedUser}', 'UserController@delete')->name('users.delete');

    // Restore a trashed user
    Route::post('/users/{trashedUser}/restore', 'UserController@restore')->name('users.restore');

});
