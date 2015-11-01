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
     * @param  $bus
     * @param  $token
     * @param  $request
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
     * Sends an email to bus that a payment has been made
     *
     * @param  $busId
     * @param  $request
     * @param  $user
     */
    public static function sendNotificationEmailToBusBookingMade($busId, $request, $user)
    {
        $region = App\Region::find($request->region_id);
        $bus    = App\Bus::find($busId);

        Mail::send('emails.booking_made', [
            'bus'       => $bus->account,
            'region'    => $region,
            'request'   => $request,
            'user'      => $user
        ],
            function($message) use ($bus){
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Booking made');
            });
    }

    public static function sendNotificationEmailToUserQuotePaid($user, $deposit, $request, $busId)
    {
        $region = App\Region::find($request->region_id);
        $bus    = App\Bus::find($busId);

        Mail::send('emails.booking_made_user', [
            'user'      => $user,
            'deposit'   => $deposit,
            'bus'       => $bus->account,
            'region'    => $region,
            'request'   => $request,
        ],
            function($message) use ($user){
                $message->to($user->email, $user->name)
                    ->subject('Booking made');
            });
    }

    /**
     * Sends email to user that a bus has made a quote for his request.
     *
     * @param  Quote $quote
     */
    public static function sendNotificationEmailToUserQuoteReceived(Quote $quote)
    {
        $bus        = $quote->bus;
        $request    = $quote->request;
        $region     = $request->region;
        $user       = $request->user;

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
