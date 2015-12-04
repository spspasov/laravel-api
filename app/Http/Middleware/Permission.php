<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AuthenticateController;
use Closure;
use App\Http\Controllers\AuthenticateController as Auth;
use App;
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
        $user   = AuthenticateController::getAuthenticatedUser();

        if ( ! $user instanceof App\User) {
            return $user;
        }
        $roles  = $user->roles;
        $role   = $roles[0]['role'];

        if ($role == 'client') {
            if ($user->accountable_id == $request->route('users')) {
                if ($request->route('requests')) {
                    if ($userRequest = \App\Request::find($request->route('requests'))) {
                        if ($userRequest->belongsToUser($user->accountable_id)) {
                            return $next($request);
                        }
                        return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
                    }
                }
                return $next($request);
            }
            if ($request->route()->getName() == 'api.regions') {
                return $next($request);
            }

            return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
        } else if ($role == 'bus') {
            if ($user->accountable_id == $request->route('buses')) {
                return $next($request);
            }
            return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
        } else if ($role == 'venue') {
          if ($user->accountable_id == $request->route('venues')) {
              return $next($request);
          }
        } else if ($role == 'admin') {
            return $next($request);
        }
        return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
    }
}
