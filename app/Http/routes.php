<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/**
 *
 * API Routes
 *
 */

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->resource('users', 'App\Http\Controllers\UserController');

    $api->get('users/{users}/requests', [
        'as'    => 'api.users.show.requests',
        'uses'  => 'App\Http\Controllers\UserController@requests'
    ]);

    $api->get('users/{users}/requests/{requests}', [
        'as'    => 'api.users.show.requests.show',
        'uses'  => 'App\Http\Controllers\RequestsController@show'
    ]);

    $api->delete('users/{users}/requests/{requests}', [
        'as' => 'api.users.show.requests.delete',
        'uses' => 'App\Http\Controllers\UserController@deleteUncompletedRequest'
    ]);

    $api->post('/requests/create', 'App\Http\Controllers\RequestsController@create');
    $api->resource('requests', 'App\Http\Controllers\RequestsController');
    $api->post('/auth/authenticate', 'App\Http\Controllers\AuthenticateController@authenticate');
    $api->get('/auth/get-auth-user', 'App\Http\Controllers\AuthenticateController@getAuthenticatedUser');
    $api->post('/auth/create', 'App\Http\Controllers\AuthenticateController@create');
});