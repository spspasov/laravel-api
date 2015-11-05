<?php

namespace App\Http\Middleware;

use App;
use Closure;
use App\Http\Controllers\AuthenticateController;

class Role
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, ...$roles)
    {

        $user = AuthenticateController::getAuthenticatedUser();

        if ( ! $user instanceof App\User) {
            return $user;
        }

        foreach($user->roles as $userRole) {
            foreach($roles as $role) {
                if ($userRole->role == $role) {

                    return $next($request);
                }
            }
        }
        return response()->json(["error" => "You don't have the required permissions to access this resource"], 403);
    }
}
