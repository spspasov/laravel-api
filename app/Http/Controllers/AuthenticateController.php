<?php

namespace App\Http\Controllers;

use App\User;
use App\Bus;
use App\Client;
use App\Role;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthenticateController extends Controller
{
    /**
     * Protect the methods that require authentication
     */
    public function __construct() 
    {

        $this->middleware('jwt.auth', ['except' => ['login', 'getAuthenticatedUser', 'create']]);
    }

    /**
     * Try to authenticate the user from the provided credentials
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        return response()->json(compact('token'));
    }

    /**
     * Get the credentials for the new user from the request
     * We can send these using the url and the following syntax:
     *
     * http://localhost:8000/api/auth/create?name=test&email=test@gmail.com&password=qwe123
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function create(Request $request) 
    {
        $userCredentials = $request->only('email', 'password', 'name', 'phone_number');

        $validator = $this->userValidator($userCredentials);

        if ($validator->fails()) {
            return response()->json(['error' => 'validation fail'], 401);
        }

        if ($request->only('terms')['terms']) {

            $busCredentials = $request->only('image_url', 'description', 'terms');

            $validator = $this->busValidator($busCredentials);

            if ($validator->fails()) {

                return response()->json(['error' => 'bus validation fail'], 401);
            }

            if ($user = $this->storeUser($userCredentials)) {
                /*
                 * Persist the bus to the database
                 */
                $bus = $this->storeBus($busCredentials);

                /*
                 * attach it to the main user account
                 */
                $bus->account()->save($user);

                /*
                 * set the account to be active by default
                 */
                $bus->account->active = User::ACTIVE;

                /*
                 * and save it
                 */
                $bus->account->save();

                /*
                 * and attach it the role of a business
                 */
                $user->roles()->attach(Role::ROLE_BUS);

                /*
                 * and save the whole thing at the end
                 */
                $user->save();
            }
        } else {

            $clientCredentials = $request->only('ip_address', 'device', 'device_token');

            $validator = $this->clientValidator($clientCredentials);

            if ($validator->fails()) {

                return response()->json(['error' => 'client validation fail'], 401);
            }

            if ($user = $this->storeUser($userCredentials)) {
                /*
                 * Persist the client to the database
                 */
                $client = $this->storeClient($clientCredentials);

                /*
                 * attach it to the main user account
                 */
                $client->account()->save($user);

                /*
                 * and attach it the role of a client
                 */
                $user->roles()->attach(Role::ROLE_CLIENT);

                /*
                 * and save the whole thing at the end
                 */
                $user->save();
            }
        }

        /**
         * TODO: Only for development purposes. Delete before going to production
         */
        return response()->json(['success' => 'true', 'user' => $userCredentials], 201);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function userValidator(array $data)
    {
        return Validator::make($data, [
            'name'          => 'required|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'password'      => 'required|min:6',
            'phone_number'  => 'required|min:6|regex:/^([0-9\s\-\+\(\)]*)$/'
        ]);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function clientValidator(array $data)
    {
        return Validator::make($data, [
            'ip_address'   => 'required',
            'device'       => 'required',
            'device_token' => 'required',
        ]);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function busValidator(array $data)
    {
        return Validator::make($data, [
            'image_url'      => 'required',
            'description'    => 'required',
            'terms'          => 'required',
        ]);
    }

    /**
     * Persist the created user to the database
     *
     * @param array $data
     * @return mixed
     */
    protected function storeUser(array $data)
    {
        return User::create([
            'name'          => $data       ['name'],
            'email'         => $data       ['email'],
            'password'      => bcrypt($data['password']),
            'phone_number'  => $data       ['phone_number']
        ]);
    }

    /**
     * Persist the created client to the database
     *
     * @param array $data
     * @return mixed
     */
    protected function storeClient(array $data)
    {
        return Client::create([
            'ip_address'    => $data['ip_address'],
            'device'        => $data['device'],
            'device_token'  => $data['device_token'],
        ]);
    }

    /**
     * Persist the created bus to the database
     *
     * @param array $data
     * @return mixed
     */
    protected function storeBus(array $data)
    {
        return Bus::create([
            'image_url'     => $data['image_url'],
            'description'   => $data['description'],
            'terms'         => $data['terms'],
        ]);
    }

    /**
     * Get the user via the sub claim from the token
     * that is passed with the request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getAuthenticatedUser()
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {

                return response()->json(['user_not_found'], 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }

        // the token is valid and we have found the user via the sub claim
        return $user;
    }

    /**
     * Check to see if the user is a bus
     *
     * @return string
     */
    public static function isBus()
    {
        $user = AuthenticateController::getAuthenticatedUser();

        if ($user->roles[0]->role == 'bus') {

            return 'true';
        }

        return 'false';
    }

    /**
     * Check to see if the user is a client and not a bus
     *
     * @return string
     */
    public static function isClient()
    {
        $user = AuthenticateController::getAuthenticatedUser();

        if ($user->roles[0]->role == 'client') {

            return 'true';
        }

        return 'false';
    }


    /**
     * Check to see if the user is an admin
     *
     * @return string
     */
    public static function isAdmin()
    {
        $user = AuthenticateController::getAuthenticatedUser();

        if ($user->roles[0]->role == 'admin') {

            return 'true';
        }

        return 'false';
    }
}