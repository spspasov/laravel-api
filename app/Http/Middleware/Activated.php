<?php namespace App\Http\Middleware;

use App\Http\Controllers\EmailsController;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Controllers\AuthenticateController as Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use App;

class Activated
{

    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth;

    /**
     * Create a new filter instance.
     *
     * @param  Guard $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::getAuthenticatedUser();

        if ( ! $user instanceof App\User) {
            return $user;
        }
        if ( ! Auth::isUserActivated()) {
            // If the user has not had an activation token set
            $token = $user->activation_token;

            if (empty($token)) {

                /*
                 * Generate an activation token.
                 */
                $token = App\Token::generateToken();

                /*
                 * Set it.
                 */
                $user->activation_token = $token;
                $user->save();
            }

            /*
             * And send it.
             */
            EmailsController::sendActivationEmailToUser($token, $user);

            return response()->json(['message' => 'Your account is not active yet. Please check your inbox.'], 403);
        }

        return $next($request);
    }
}