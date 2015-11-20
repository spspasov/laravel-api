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
use App\Http\Requests\Request;

/*
 |-----------------------------------------------------------------------------
 | Application Routes
 |-----------------------------------------------------------------------------
 */

/*
 * Just a very simple view for resetting the password
 */
Route::get('/password/reset/{token}', 'Auth\PasswordController@getReset');

/*
 |-------------------------------------------------------------------------
 | Activation controller
 |-------------------------------------------------------------------------
*/

Route::get('/activation/{token?}', 'Auth\ActivationController@getActivate');
Route::post('/activation/{token?}', 'Auth\ActivationController@postActivate');
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
    $api->group(['middleware' => ['role:client,admin', 'permission']], function ($api) {

        /*
         * Admin
         */
        $api->resource('users', 'App\Http\Controllers\UserController', ['only' => ['index', 'show']]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->get('users/{users}/requests', [
            'as'    => 'api.users.requests',
            'uses'  => 'App\Http\Controllers\UserController@requests'
        ]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->get('users/{users}/requests/{requests}', [
            'as'    => 'api.users.requests.show',
            'uses'  => 'App\Http\Controllers\RequestsController@show'
        ]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->delete('users/{users}/requests/{requests}', [
            'as' => 'api.users.requests.destroy',
            'uses' => 'App\Http\Controllers\UserController@deleteUncompletedRequest'
        ]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->get('users/{users}/requests/{requests}/quotes', [
            'as'    => 'api.users.requests.quotes',
            'uses'  => 'App\Http\Controllers\RequestsController@quotes'
        ]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->get('users/{users}/requests/{requests}/quotes/{quotes}', [
            'as'    => 'api.users.requests.quotes.show',
            'uses'  => 'App\Http\Controllers\QuotesController@showQuoteForUser'
        ]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->get('users/{users}/requests/{requests}/quotes/{quotes}/pay', [
            'as'    => 'api.users.requests.quotes.pay',
            'uses'  => 'App\Http\Controllers\QuotesController@getPayQuote'
        ]);

        /*
         * Okay
         *
         * User, admin
         */
        $api->post('users/{users}/requests/{requests}/quotes/{quotes}/pay', [
            'as'    => 'api.users.requests.quotes.pay',
            'uses'  => 'App\Http\Controllers\QuotesController@postPayQuote'
        ]);
    });

    /*
     |-------------------------------------------------------------------------
     | Bus routes
     |-------------------------------------------------------------------------
     */

    $api->group(['middleware' => ['role:bus,admin', 'permission']], function ($api) {

        $api->get('buses/{buses}/requests', [
            'as'    => 'api.buses.requests',
            'uses'  => 'App\Http\Controllers\BusesController@allRequestsForBusFromSameRegions'
        ]);

        $api->get('buses/{buses}/requests/{requests}', [
            'as'    => 'api.buses.requests.show',
            'uses'  => 'App\Http\Controllers\BusesController@showRequestFromSameRegionAsBus'
        ]);

        $api->get('buses/{buses}/requests/{requests}/quotes', [
            'as'    => 'api.buses.requests.quotes.show',
            'uses'  => 'App\Http\Controllers\QuotesController@showQuoteForBus'
        ]);

        $api->post('buses/{buses}/requests/{requests}/quotes', [
            'as'    => 'api.buses.requests.quotes',
            'uses'  => 'App\Http\Controllers\QuotesController@create'
        ]);

        $api->get('buses/{buses}/quotes/', [
            'as'    => 'api.buses.quotes',
            'uses'  => 'App\Http\Controllers\QuotesController@index'
        ]);

        $api->post('buses/{buses}/quotes', [
            'as'    => 'api.buses.quotes.store',
            'uses'  => 'App\Http\Controllers\QuotesController@store'
        ]);


        $api->get('buses/{buses}/quotes/accepted', [
            'as'    => 'api.buses.quotes.accepted',
            'uses'  => 'App\Http\Controllers\BusesController@showQuotesWithTransaction'
        ]);

        $api->get('buses/{buses}/quotes/{quotes}', [
            'as'    => 'api.buses.quotes.show',
            'uses'  => 'App\Http\Controllers\QuotesController@show'
        ]);

        $api->delete('buses/{buses}/quotes/{quotes}', [
            'as'    => 'api.buses.quotes.show.delete',
            'uses'  => 'App\Http\Controllers\QuotesController@destroy'
        ]);

        $api->get('buses/{buses}/regions/', [
            'as'    => 'api.buses.regions',
            'uses'  => 'App\Http\Controllers\BusesController@listRegions'
        ]);

        $api->post('buses/{buses}/regions/subscribe/{regions}', [
            'as'    => 'api.buses.regions.subscribe',
            'uses'  => 'App\Http\Controllers\BusesController@subscribeToRegion'
        ]);

        $api->delete('buses/{buses}/regions/unsubscribe/{regions}', [
            'as'    => 'api.buses.regions.unsubscribe',
            'uses'  => 'App\Http\Controllers\BusesController@unsubscribeFromRegion'
        ]);

        $api->patch('buses/{buses}', [
            'as'    => 'api.buses.update',
            'uses'  => 'App\Http\Controllers\BusesController@update'
        ]);

        $api->resource('buses', 'App\Http\Controllers\BusesController', ['only' => ['index', 'show']]);
    });

     /*
     |-------------------------------------------------------------------------
     | Requests routes
     |-------------------------------------------------------------------------
     */

    $api->group(['middleware' => ['activated', 'role:client,admin']], function ($api) {

        $api->post('requests', [
                'as'    => 'api.requests',
                'uses'  => 'App\Http\Controllers\RequestsController@create'
            ]);
    });

    /*
     * This route is used only in the email
     * It passes the token that buses use to authenticate
     * And it shows the requested resource
     */
    $api->get('requests/{requests}/{bus}', [
            'as'    => 'api.requests.email',
            'uses'  => 'App\Http\Controllers\RequestsController@showRequestFromSameRegionAsBus'
    ]);

    /*
     * Admin only route that shows all the requests
     */
    $api->group(['middleware' => 'role:admin'], function ($api) {
        $api->get('requests', [
            'as'     => 'api.requests',
            'uses'   => 'App\Http\Controllers\RequestsController@index'
        ]);
    });

    /*
    |-------------------------------------------------------------------------
    | Quotes routes
    |-------------------------------------------------------------------------
   */
    $api->group(['middleware' => ['activated', 'role:bus,admin']], function ($api) {
        $api->post('quotes', [
            'as' => 'api.quotes',
            'uses' => 'App\Http\Controllers\QuotesController@create'
        ]);
    });
     /*
     |-------------------------------------------------------------------------
     | Auth routes
     |-------------------------------------------------------------------------
    */

    /*
     * Sign in with the given credentials
     */
    $api->post('/auth/login', [
        'as'    => 'api.auth.login',
        'uses'  => 'App\Http\Controllers\AuthenticateController@login']);

    /*
     * Get the currently authenticated user
     */
    $api->get('/auth/user', [
        'as'    => 'api.auth.user',
        'uses'  => 'App\Http\Controllers\AuthenticateController@getAuthenticatedUser']);

    /*
     * Create a new account
     */
    $api->post('/auth/register', [
        'as'    => 'api.auth.register',
        'uses'  => 'App\Http\Controllers\AuthenticateController@create']);

    /*
     |-------------------------------------------------------------------------
     | Password controller
     |-------------------------------------------------------------------------
    */

    /*
     * Send the reset link to the user via email.
     */
    $api->post('/password/email', [
        'as'    => 'password.email',
        'uses'  => 'App\Http\Controllers\Auth\PasswordController@postEmail'
    ]);

    /*
     * Reset the given user's password and send it to the bottom route.
     */
    $api->post('/password/reset', [
        'as'    => 'password.reset',
        'uses'  => 'App\Http\Controllers\Auth\PasswordController@postReset'
    ]);

    /*
     * Just a confirmation message that the mail was send successfully.
     */
    $api->get('/password/success', function() {

       return "Successfully reset password!";
    });

    /*
     |-------------------------------------------------------------------------
     | Misc routes
     |-------------------------------------------------------------------------
*/

});