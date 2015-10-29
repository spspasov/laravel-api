<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Quote;
use App\Bus;
use App\User;
use App;

class EmailsController extends Controller
{
    /**
     * Send a notifying email to buses, containing auth link as well.
     *
     * @param $bus
     * @param $token
     * @param $request
     */
    public static function sendAuthEmailToBusWithRequestDetails($bus, $token, $request)
    {
        Mail::send('emails.quote_request', [
            'bus' => $bus,
            'token' => $token,
            'request' => $request
        ],
            function($message) use ($bus){
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Quote request');
            });
    }

    public static function sendNotificationEmailToUserRegardingQuote(Quote $quote)
    {
        $bus        = Bus::find($quote->bus_id);
        $request    = App\Request::find($quote->request_id);
        $user       = User::find($request->user_id);

        Mail::send('emails.quote_received', [
            'bus'       => $bus,
            'request'   => $request,
            'user'      => $user
        ],
            function($message) use ($user){
                $message->to($user->email, $user->name)
                    ->subject('Quote received');
            });
    }
}
