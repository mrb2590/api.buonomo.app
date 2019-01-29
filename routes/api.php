<?php

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
Route::prefix('v1')->group(function () {

    /* Oauth Proxy */

    // Fetch access token
    Route::post('/oauth/proxy/token', 'Auth\LoginController@fetchToken')->name('oauth.proxy.token');

    /* Users */

    // Fetch the current user
    Route::get('/user', 'UserController@fetchCurrent')->name('user.fetch');

    // Fetch all or a single user
    Route::get('/users/{username?}', 'UserController@fetch')->name('users.fetch');

    // Create a user
    Route::post('/users', 'UserController@store')->name('users.create');

    // Update a user's profile
    Route::patch('/users/{user}/profile', 'UserController@updateProfile')
        ->name('users.profile.update');

    // Update a user's email
    Route::patch('/users/{user}/email', 'UserController@updateEmail')->name('users.email.update');

    // Update a user's password
    Route::patch('/users/{user}/password', 'UserController@updatePassword')
        ->name('users.password.update');

    // Trash a user
    Route::delete('/users/{user}/trash', 'UserController@trash')->name('users.trash');

    // Delete a trashed user
    Route::delete('/users/{trashedUser}', 'UserController@delete')->name('users.delete');

    // Restore a trashed user
    Route::post('/users/{trashedUser}/restore', 'UserController@restore')->name('users.restore');

    /* User Aggreated Data */

    // Fetch the current user
    Route::get('/users/aggregate/created', 'UserAggregateController@fetchCreated')
        ->name('users.aggregate.created.fetch');

    /* Roles */

    // Fetch the all roles
    Route::get('/roles', 'RoleController@fetch')->name('roles.fetch');

    /* User roles */

    // Fetch the current user's roles
    Route::get('/user/roles', 'UserRoleController@fetchCurrent')->name('user.roles.fetch');

    // Fetch a single user's roles
    Route::get('/users/{user}/roles', 'UserRoleController@fetch')->name('users.roles.fetch');

    // Assign a user a role
    Route::post('/users/{user}/roles', 'UserRoleController@assign')->name('users.roles.assign');

    // Remove a user's role
    Route::delete('/users/{user}/roles', 'UserRoleController@remove')->name('users.roles.remove');

    /* Avatars */

    // Fetch the current user's avatar
    Route::get('/user/avatar', 'AvatarController@fetchCurrent')->name('user.avatar.fetch');

    // Create an avatar
    Route::post('/avatars', 'AvatarController@store')->name('avatar.create');

    // Fetch avatar options
    Route::get('/avatars/options', 'AvatarController@fetchOptions')->name('avatar.options.fetch');

    // Update an  avatar
    Route::patch('/users/{user}/avatar', 'AvatarController@update')
        ->name('users.avatar.update');

    /* Drive Server */

    // Fetch server data
    Route::get('/drive/server', 'Drive\ServerController@fetch')
        ->name('drive.server.fetch');

    /* Drive Folders */

    // Fetch the current user's folder
    Route::get('/user/drive/folder', 'Drive\FolderController@fetchCurrent')
        ->name('user.drive.folder.fetch');

        // Fetch the current user's folder tree
        Route::get('/user/drive/folder/tree', 'Drive\FolderController@fetchCurrentTree')
            ->name('user.drive.folder.tree.fetch');

    // Fetch all or a single folder
    Route::get('/drive/folders/{folder?}', 'Drive\FolderController@fetch')
        ->name('drive.folders.fetch');

    // Fetch all folders in a folder
    Route::get('/drive/folders/{folder}/folders', 'Drive\FolderController@fetchFolders')
        ->name('drive.folders.children.fetch');

    // Fetch all files in a folder
    Route::get('/drive/folders/{folder}/files', 'Drive\FolderController@fetchFiles')
        ->name('drive.folders.files.fetch');

    // Fetch all files and folders in a folder
    Route::get('/drive/folders/{folder}/list', 'Drive\FolderController@fetchList')
        ->name('drive.folders.list.fetch');

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

    /* Drive Files */

    // Fetch all or a single file
    Route::get('/drive/files/{file?}', 'Drive\FileController@fetch')
        ->name('drive.files.fetch');

    // Create a file
    Route::post('/drive/files', 'Drive\FileController@store')->name('drive.files.create');

    // Update a file
    Route::patch('/drive/files/{file}', 'Drive\FileController@update')
        ->name('drive.files.update');

    // Move a file
    Route::patch('/drive/files/{file}/move', 'Drive\FileController@move')
        ->name('drive.files.move');

    // Download a file
    Route::get('/drive/files/{file}/download', 'Drive\FileController@download')
        ->name('drive.files.download');

    // Trash a file
    Route::delete('/drive/files/{file}/trash', 'Drive\FileController@trash')
        ->name('drive.files.trash');

    // Delete a trashed file
    Route::delete('/drive/files/{trashedFile}', 'Drive\FileController@delete')
        ->name('drive.files.delete');

    // Restore a trashed file
    Route::post('/drive/files/{trashedFile}/restore', 'Drive\FileController@restore')
        ->name('drive.files.restore');

    /* Surveillance */

    // Stream the camera feed
    Route::get('/surveillance/cameras/{camera}', 'Surveillance\CameraController@streamFeed');

    // Run lights program
    Route::post(
        '/surveillance/webhook/motion-detected',
        'Surveillance\CameraController@motionWebhook'
    );

    /* RaspberryPi */

    // Run lights program webhook for Google Assistant app
    Route::post(
        '/google/raspberrypi-controller/webhook',
        'RaspberryPi\LightController@runLightsProgramWebhook'
    );

});
