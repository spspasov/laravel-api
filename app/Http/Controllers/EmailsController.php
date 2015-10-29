<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Region;
use App\Quote;
use App\User;
use App\Bus;
use App;

class EmailsController extends Controller
{
    /**
     * Sends a notifying email to buses, containing auth link as well.
     *
     * @param $bus
     * @param $token
     * @param $request
     */
    public static function sendAuthEmailToBusWithRequestDetails($bus, $token, $request)
    {
        Mail::send('emails.quote_request', [
            'bus'       => $bus,
            'token'     => $token,
            'request'   => $request
        ],
            function($message) use ($bus){
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Quote request');
            });
    }

    /**
     * Sends email to user that a bus has made a quote for his request.
     *
     * @param Quote $quote
     */
    public static function sendNotificationEmailToUserRegardingQuote(Quote $quote)
    {
        $bus        = Bus::find($quote->bus_id);
        $request    = App\Request::find($quote->request_id);
        $region     = Region::find($request->region_id);
        $user       = User::find($request->user_id);

        Mail::send('emails.quote_received', [
            'bus'       => $bus,
            'request'   => $request,
            'user'      => $user,
            'region'    => $region
        ],
            function($message) use ($user){
                $message->to($user->email, $user->name)
                    ->subject('Quote received');
            });
    }
}
