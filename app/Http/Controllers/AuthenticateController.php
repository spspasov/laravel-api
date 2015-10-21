<?php

namespace App\Http\Controllers;

use App\User;
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
    public function __construct() {

        $this->middleware('jwt.auth', ['except' => ['authenticate', 'getAuthenticatedUser']]);
    }

    /**
     * TODO: Do not use the request to get the data
     */

    /**
     * Try to authenticate the user from the provided credentials
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(Request $request)
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
     *
     * We can send these using the url and the following syntax:
     *
     * http://localhost:8000/api/auth/create?name=test&email=test@gmail.com&password=qwe123
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {

        $credentials = $request->only('email', 'password', 'name');

        $validator = $this->validator($credentials);

        if ($validator->fails()) {
            return response()->json(['error' => 'validation fail'], 401);
        }

        $this->store($credentials);

        /**
         * TODO: Only for development purposes. Delete before going to production
         */
        return response()->json(['success' => 'true', 'user' => $credentials], 201);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'          => 'required|max:255',
            'email'         => 'required|email|max:255|unique:users',
            'password'      => 'required|min:6'
        ]);
    }

    /**
     * Persist the created user to the database
     *
     * @param array $data
     * @return mixed
     */
    protected function store(array $data)
    {
        return User::create([
            'name'          => $data       ['name'],
            'email'         => $data       ['email'],
            'password'      => bcrypt($data['password']),
        ]);
    }

    public static function getAuthenticatedUser()
    {

        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}