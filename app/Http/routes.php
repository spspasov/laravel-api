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


/*
 |-----------------------------------------------------------------------------
 | API Routes
 |-----------------------------------------------------------------------------
 */

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

     /*
     |-------------------------------------------------------------------------
     | Users routes
     |-------------------------------------------------------------------------
     */
    $api->group(['middleware' => 'role:client,admin'], function ($api) {

        $api->resource('users', 'App\Http\Controllers\UserController');

        $api->get('users/{users}/requests', [
            'as' => 'api.users.show.requests',
            'uses' => 'App\Http\Controllers\UserController@requests'
        ]);

        $api->get('users/{users}/requests/{requests}', [
            'as' => 'api.users.show.requests.show',
            'uses' => 'App\Http\Controllers\RequestsController@show'
        ]);

        $api->delete('users/{users}/requests/{requests}', [
            'as' => 'api.users.show.requests.delete',
            'uses' => 'App\Http\Controllers\UserController@deleteUncompletedRequest'
        ]);

        $api->get('users/{users}/requests/{requests}/quotes', [
            'as' => 'api.users.show.requests.show.quotes',
            'uses' => 'App\Http\Controllers\RequestsController@quotes'
        ]);

        $api->get('users/{users}/requests/{requests}/quotes/{quotes}', [
            'as' => 'api.users.show.requests.show.quotes',
            'uses' => 'App\Http\Controllers\QuotesController@show'
        ]);
    });

    /*
     |-------------------------------------------------------------------------
     | Bus routes
     |-------------------------------------------------------------------------
     */
    $api->group(['middleware' => 'role:bus,admin'], function ($api) {

        $api->get('buses/{buses}/requests', [
            'as' => 'api.buses.show.requests',
            'uses' => 'App\Http\Controllers\BusesController@requestsForBusFromSameRegions'
        ]);

        $api->get('buses/{buses}/requests/{requests}', [
            'as' => 'api.buses.show.requests.show',
            'uses' => 'App\Http\Controllers\RequestsController@showRequestFromSameRegionAsBus'
        ]);

        $api->resource('buses', 'App\Http\Controllers\BusesController');
    });

     /*
     |-------------------------------------------------------------------------
     | Requests routes
     |-------------------------------------------------------------------------
     */
    $api->post('requests/create', 'App\Http\Controllers\RequestsController@create');
    $api->resource('requests', 'App\Http\Controllers\RequestsController');

     /*
     |-------------------------------------------------------------------------
     | Auth routes
     |-------------------------------------------------------------------------
    */
    $api->post('/auth/login', 'App\Http\Controllers\AuthenticateController@login');
    $api->get('/auth/get-auth-user', 'App\Http\Controllers\AuthenticateController@getAuthenticatedUser');
    $api->post('/auth/create', 'App\Http\Controllers\AuthenticateController@create');

    /*
     |-------------------------------------------------------------------------
     | Password controller
     |-------------------------------------------------------------------------
    */

    /*
     * Display the form for resetting and send it to the bottom route.
     */
    $api->get('/password/email', 'App\Http\Controllers\Auth\PasswordController@getEmail');

    /*
     * Send the reset link to the user via email.
     */
    $api->post('/password/email', 'App\Http\Controllers\Auth\PasswordController@postEmail');

    /*
     * Display the password reset view for the given token.
     */
    $api->get('/password/reset', 'App\Http\Controllers\Auth\PasswordController@getReset');

    /*
     * Reset the given user's password and send it to the bottom route.
     */
    $api->post('/password/reset', 'App\Http\Controllers\Auth\PasswordController@postReset');

});