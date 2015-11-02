<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthenticateController;
use Closure;
use App\Http\Controllers\AuthenticateController as Auth;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $roles  = AuthenticateController::getAuthenticatedUser()->roles;
        $role   = $roles[0]['role'];

        if ($role == 'client') {
            if (Auth::getAuthenticatedUser()->id == $request->route('users')) {
                if ($request->route('requests')) {
                    if ($userRequest = \App\Request::find($request->route('requests'))) {
                        if ($userRequest->belongsToUser(Auth::getAuthenticatedUser()->id)) {
                            return $next($request);
                        }
                        return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
                    }
                }
                return $next($request);
            }
            return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
        } else if ($role == 'bus') {
            if (Auth::getAuthenticatedUser()->accountable_id == $request->route('buses')) {
                return $next($request);
            }
            return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
        } else if ($role == 'admin') {
            return $next($request);
        }
        return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
    }
}
