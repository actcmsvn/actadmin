<?php

use ACT\Actadmin\Events\Routing;
use ACT\Actadmin\Events\RoutingAdmin;
use ACT\Actadmin\Events\RoutingAdminAfter;
use ACT\Actadmin\Events\RoutingAfter;
use ACT\Actadmin\Models\DataType;

/*
|--------------------------------------------------------------------------
| Actadmin Routes
|--------------------------------------------------------------------------
|
| This file is where you may override any of the routes that are included
| with actadmin.
|
*/

Route::group(['as' => 'actadmin.'], function () {
    event(new Routing());

    $namespacePrefix = '\\'.config('actadmin.controllers.namespace').'\\';

    Route::get('login', ['uses' => $namespacePrefix.'ActadminAuthController@login',     'as' => 'login']);
    Route::post('login', ['uses' => $namespacePrefix.'ActadminAuthController@postLogin', 'as' => 'postlogin']);

    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
        event(new RoutingAdmin());

        // Main Admin and Logout Route
        Route::get('/', ['uses' => $namespacePrefix.'ActadminController@index',   'as' => 'dashboard']);
        Route::post('logout', ['uses' => $namespacePrefix.'ActadminController@logout',  'as' => 'logout']);
        Route::post('upload', ['uses' => $namespacePrefix.'ActadminController@upload',  'as' => 'upload']);

        Route::get('profile', ['uses' => $namespacePrefix.'ActadminController@profile', 'as' => 'profile']);

        try {
            foreach (DataType::all() as $dataType) {
                $breadController = $dataType->controller
                                 ? $dataType->controller
                                 : $namespacePrefix.'ActadminBaseController';

                Route::get($dataType->slug.'/order', $breadController.'@order')->name($dataType->slug.'.order');
                Route::post($dataType->slug.'/order', $breadController.'@update_order')->name($dataType->slug.'.order');
                Route::resource($dataType->slug, $breadController);
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
        } catch (\Exception $e) {
            // do nothing, might just be because table not yet migrated.
        }

        // Role Routes
        Route::resource('roles', $namespacePrefix.'ActadminRoleController');

        // Menu Routes
        Route::group([
            'as'     => 'menus.',
            'prefix' => 'menus/{menu}',
        ], function () use ($namespacePrefix) {
            Route::get('builder', ['uses' => $namespacePrefix.'ActadminMenuController@builder',    'as' => 'builder']);
            Route::post('order', ['uses' => $namespacePrefix.'ActadminMenuController@order_item', 'as' => 'order']);

            Route::group([
                'as'     => 'item.',
                'prefix' => 'item',
            ], function () use ($namespacePrefix) {
                Route::delete('{id}', ['uses' => $namespacePrefix.'ActadminMenuController@delete_menu', 'as' => 'destroy']);
                Route::post('/', ['uses' => $namespacePrefix.'ActadminMenuController@add_item',    'as' => 'add']);
                Route::put('/', ['uses' => $namespacePrefix.'ActadminMenuController@update_item', 'as' => 'update']);
            });
        });

        // Settings
        Route::group([
            'as'     => 'settings.',
            'prefix' => 'settings',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'ActadminSettingsController@index',        'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix.'ActadminSettingsController@store',        'as' => 'store']);
            Route::put('/', ['uses' => $namespacePrefix.'ActadminSettingsController@update',       'as' => 'update']);
            Route::delete('{id}', ['uses' => $namespacePrefix.'ActadminSettingsController@delete',       'as' => 'delete']);
            Route::get('{id}/move_up', ['uses' => $namespacePrefix.'ActadminSettingsController@move_up',      'as' => 'move_up']);
            Route::get('{id}/move_down', ['uses' => $namespacePrefix.'ActadminSettingsController@move_down',    'as' => 'move_down']);
            Route::get('{id}/delete_value', ['uses' => $namespacePrefix.'ActadminSettingsController@delete_value', 'as' => 'delete_value']);
        });

        // Admin Media
        Route::group([
            'as'     => 'media.',
            'prefix' => 'media',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'ActadminMediaController@index',              'as' => 'index']);
            Route::post('files', ['uses' => $namespacePrefix.'ActadminMediaController@files',              'as' => 'files']);
            Route::post('new_folder', ['uses' => $namespacePrefix.'ActadminMediaController@new_folder',         'as' => 'new_folder']);
            Route::post('delete_file_folder', ['uses' => $namespacePrefix.'ActadminMediaController@delete_file_folder', 'as' => 'delete_file_folder']);
            Route::post('directories', ['uses' => $namespacePrefix.'ActadminMediaController@get_all_dirs',       'as' => 'get_all_dirs']);
            Route::post('move_file', ['uses' => $namespacePrefix.'ActadminMediaController@move_file',          'as' => 'move_file']);
            Route::post('rename_file', ['uses' => $namespacePrefix.'ActadminMediaController@rename_file',        'as' => 'rename_file']);
            Route::post('upload', ['uses' => $namespacePrefix.'ActadminMediaController@upload',             'as' => 'upload']);
            Route::post('remove', ['uses' => $namespacePrefix.'ActadminMediaController@remove',             'as' => 'remove']);
            Route::post('crop', ['uses' => $namespacePrefix.'ActadminMediaController@crop',             'as' => 'crop']);
        });

        // BREAD Routes
        Route::group([
            'as'     => 'bread.',
            'prefix' => 'bread',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'ActadminBreadController@index',              'as' => 'index']);
            Route::get('{table}/create', ['uses' => $namespacePrefix.'ActadminBreadController@create',     'as' => 'create']);
            Route::post('/', ['uses' => $namespacePrefix.'ActadminBreadController@store',   'as' => 'store']);
            Route::get('{table}/edit', ['uses' => $namespacePrefix.'ActadminBreadController@edit', 'as' => 'edit']);
            Route::put('{id}', ['uses' => $namespacePrefix.'ActadminBreadController@update',  'as' => 'update']);
            Route::delete('{id}', ['uses' => $namespacePrefix.'ActadminBreadController@destroy',  'as' => 'delete']);
            Route::post('relationship', ['uses' => $namespacePrefix.'ActadminBreadController@addRelationship',  'as' => 'relationship']);
            Route::get('delete_relationship/{id}', ['uses' => $namespacePrefix.'ActadminBreadController@deleteRelationship',  'as' => 'delete_relationship']);
        });

        // Database Routes
        Route::resource('database', $namespacePrefix.'ActadminDatabaseController');

        // Compass Routes
        Route::group([
            'as'     => 'compass.',
            'prefix' => 'compass',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'ActadminCompassController@index',  'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix.'ActadminCompassController@index',  'as' => 'post']);
        });

        event(new RoutingAdminAfter());
    });
    event(new RoutingAfter());
});
