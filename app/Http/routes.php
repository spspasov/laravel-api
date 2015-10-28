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
    $api->group(['middleware' => 'role:client,admin'], function ($api) {

        $api->resource('users', 'App\Http\Controllers\UserController', ['only' => ['index', 'show']]);

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
            'as' => 'api.users.show.requests.show.quotes.show',
            'uses' => 'App\Http\Controllers\QuotesController@showQuoteForUser'
        ]);
    });

    /*
     |-------------------------------------------------------------------------
     | Bus routes
     |-------------------------------------------------------------------------
     */
    $api->group(['middleware' => 'role:bus,admin'], function ($api) {

        $api->get('buses/{buses}/requests', [
            'as'    => 'api.buses.requests',
            'uses'  => 'App\Http\Controllers\BusesController@allRequestsForBusFromSameRegions'
        ]);

        $api->get('buses/{buses}/requests/{requests}', [
            'as'    => 'api.buses.requests.show',
            'uses'  => 'App\Http\Controllers\BusesController@showRequestFromSameRegionAsBus'
        ]);

        $api->get('buses/{buses}/quotes', [
            'as'    => 'api.buses.quotes',
            'uses'  => 'App\Http\Controllers\QuotesController@index'
        ]);

        $api->resource('buses', 'App\Http\Controllers\BusesController', ['only' => ['index', 'show']]);
    });

     /*
     |-------------------------------------------------------------------------
     | Requests routes
     |-------------------------------------------------------------------------
     */
    $api->post('requests/create',
        ['middleware' => 'activated'],
        'App\Http\Controllers\RequestsController@create');

    /*
     * This route is used only in the email
     * It passes the token that buses use to authenticate
     * And it shows the requested resource
     */
    $api->get('requests/{requests}/{bus}/{token?}', 'App\Http\Controllers\RequestsController@showRequestFromSameRegionAsBus');

    $api->resource('requests', 'App\Http\Controllers\RequestsController', ['except' => ['edit', 'update']]);

    /*
    |-------------------------------------------------------------------------
    | Quotes routes
    |-------------------------------------------------------------------------
   */

    $api->post('buses/{buses}/quotes', 'App\Http\Controllers\QuotesController@store');

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
     * Send the reset link to the user via email.
     */
    $api->post('/password/email', 'App\Http\Controllers\Auth\PasswordController@postEmail');



    /*
     * Reset the given user's password and send it to the bottom route.
     */
    $api->post('/password/reset', 'App\Http\Controllers\Auth\PasswordController@postReset');

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

    $api->get('test', function()
    {
        Request::capture();

    });
});