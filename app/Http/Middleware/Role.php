<?php

namespace App\Http\Middleware;

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

        foreach($user->roles as $userRole) {
            foreach($roles as $role) {
                if ($userRole->role == $role) {

                    return $next($request);
                }
            }
        }

        return response()->json(['You shall not pass!'], 403);
    }
}
