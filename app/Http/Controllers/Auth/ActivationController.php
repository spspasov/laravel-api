<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
}
