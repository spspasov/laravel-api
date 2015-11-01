<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\AuthenticateController;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Http\Controllers\AuthenticateController as Auth;
use App\User;

class ActivationController extends Controller
{
    /**
     * Display the account activation view for the given token.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function getActivate($token = null)
    {
        if (is_null($token)) {
            throw new NotFoundHttpException;
        }

        return view('auth.activate')->with('token', $token);
    }

    /**
     * Activate the given user with the given token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postActivate(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        $token  = $request->only('token')['token'];
        $user   = User::where('activation_token', $token)->first();

        if ($this->activate($user, $token)) {
            return response()->json(['success' => 'account successfully activated'], 200);
        }
        return response()->json(['failure' => "account couldn't be activated"], 400);
    }

    /**
     * Activate the user if the tokens match
     *
     * @param $user
     * @param $token
     */
    public function activate($user, $token)
    {
        $user->activation_token == $token ? $user->active = User::ACTIVE : false;

        return $user->save();
    }
}
