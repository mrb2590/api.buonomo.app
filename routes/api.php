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

    // Fetch the current user
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

    /* User roles */

    // Fetch the current user's roles
    Route::get('/user/roles', 'UserRoleController@fetchCurrent')->name('user.roles.fetch');

    // Fetch a single user's roles
    Route::get('/users/{user}/roles', 'UserRoleController@fetch')->name('users.roles.fetch');

    // Assign a user a role
    Route::post('/users/{user}/roles', 'UserRoleController@assign')->name('users.roles.assign');

    // Remove a user's role
    Route::delete('/users/{user}/roles', 'UserRoleController@remove')->name('users.roles.remove');

    /* Drive Folders */

    // Fetch the current user's folder
    Route::get('/user/drive/folder', 'Drive\FolderController@fetchCurrent')
        ->name('user.drive.folder.fetch');

    // Fetch all or a single folder
    Route::get('/drive/folders/{folder?}', 'Drive\FolderController@fetch')
        ->name('drive.folders.fetch');

    // Fetch all child folders of a folder
    Route::get('/drive/folders/{folder}/folders', 'Drive\FolderController@fetchChildren')
        ->name('drive.folders.children.fetch');

    // Create a folder
    Route::post('/drive/folders', 'Drive\FolderController@store')->name('drive.folders.create');

    // Update a folder
    Route::patch('/drive/folders/{folder}', 'Drive\FolderController@update')
        ->name('drive.folders.update');

    // Move a folder
    Route::patch('/drive/folders/{folder}/move', 'Drive\FolderController@move')
        ->name('drive.folders.move');

    // Download a folder as a zip
    Route::get('/drive/folders/{folder}/download', 'Drive\FolderController@download')
        ->name('drive.folders.download');

    // Trash a folder
    Route::delete('/drive/folders/{folder}/trash', 'Drive\FolderController@trash')
        ->name('drive.folders.trash');

    // Delete a trashed folder
    Route::delete('/drive/folders/{trashedFolder}', 'Drive\FolderController@delete')
        ->name('drive.folders.delete');

    // Restore a trashed folder
    Route::post('/drive/folders/{trashedFolder}/restore', 'Drive\FolderController@restore')
        ->name('drive.folders.restore');

});
