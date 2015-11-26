<?php namespace App\Http\Middleware;

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
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::getAuthenticatedUser();

        if (!$user instanceof App\User) {
            return $user;
        }
        if (!Auth::isUserActivated()) {
            // If the user has not had an activation token set
            $activation_token = $user->activation_token;

            if (empty($activation_token)) {

                /*
                 * Generate an activation token.
                 */
                $key = Config::get('app.key');
                $activation_token = hash_hmac('sha256', str_random(40), $key);

                /*
                 * Set it.
                 */
                $user->activation_token = $activation_token;
                $user->save();
            }

            /*
             * And send it.
             */
            Mail::send('emails.activate', [
                'token' => $activation_token,
                'name'  => $user->name,
            ],
                function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                        ->subject('Account activation');
                });
            return response()->json(['message' => 'Your account is not active yet. Please check your inbox.'], 403);
        }

        return $next($request);
    }
}