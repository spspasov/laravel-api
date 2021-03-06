<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests;

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
        Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.quote_request', [
            'bus'     => $bus,
            'token'   => $token,
            'request' => $request,
        ],
            function ($message) use ($bus) {
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Quote request');
            });
    }

    /**
     * Sends an email to bus that a payment has been made.
     *
     * @param  $busId
     * @param  $request
     * @param  $user
     */
    public static function sendNotificationEmailToBusBookingMade($busId, $request, $user)
    {
        $region = App\Region::find($request->region_id);
        $bus = App\Bus::find($busId);

        Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.booking_made', [
            'bus'     => $bus->account,
            'region'  => $region,
            'request' => $request,
            'user'    => $user,
        ],
            function ($message) use ($bus) {
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Booking made')
                    ->bcc(config('mail.admin_email'));
            });
    }

    /**
     * Sends a notification email to user containing info about their payment.
     * This is to be thought of as the receipt.
     *
     * @param $user
     * @param $deposit
     * @param $request
     * @param $busId
     */
    public static function sendNotificationEmailToUserQuotePaid($user, $deposit, $request, $busId)
    {
        $region = App\Region::find($request->region_id);
        $bus = App\Bus::find($busId);

        Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.booking_made_user', [
            'user'    => $user,
            'deposit' => $deposit,
            'bus'     => $bus->account,
            'region'  => $region,
            'request' => $request,
        ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Booking made');
            });
    }

    /**
     * Sends email to user that a bus has made a quote for his request.
     *
     * @param  Quote $quote
     */
    public static function sendNotificationEmailToUserQuoteReceived(App\Quote $quote)
    {
        $bus = $quote->bus;
        $request = $quote->request;
        $region = $request->region;
        $user = $request->user;

        Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.quote_received', [
            'bus'     => $bus,
            'request' => $request,
            'user'    => $user,
            'region'  => $region,
        ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Quote received');
            });
    }

    /**
     * Send a notification email to venue that the booking was cancelled.
     *
     * @param App\Booking $booking
     */
    public static function sendNotificationEmailToVenueBookingCancelled(App\Booking $booking)
    {
        $venue = $booking->venue;
        $client = $booking->client->account->name;
        $date = App\Hour::prettifyDate($booking->date);

        Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.booking_cancelled', [
            'venue'  => $venue->account->name,
            'client' => $client,
            'date'   => $date,
        ],
            function ($message) use ($venue) {
                $message->to($venue->account->email, $venue->account->name)
                    ->subject('Booking cancelled');
            });
    }

    /**
     * Send a claim email to venue that will contain a token.
     *
     * @param App\Venue $venue
     * @param           $token
     */
    public static function sendClaimEmailToVenue(App\Venue $venue, $token)
    {
        return Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.claim', [
            'venue' => $venue,
            'token' => $token,
        ],
            function ($message) use ($venue) {
                $message->to($venue->account->email, $venue->account->name)
                    ->subject('Please claim venue');
            });
    }

    /**
     * Send an email to booking that a booking was made.
     *
     * @param App\Venue   $venue
     * @param App\Booking $booking
     * @param             $token
     * @return mixed
     */
    public static function  sendNotificationEmailToVenueBookingMade(App\Venue $venue, App\Booking $booking, $token)
    {
        return Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.booking_made_venue', [
            'venue'   => $venue,
            'booking' => $booking,
            'token'   => $token->token,
        ],
            function ($message) use ($venue) {
                $message->to($venue->account->email, $venue->account->name)
                    ->subject('Booking made');
            });
    }


    /**
     * Send a notification email to user that his booking has been accepted.
     *
     * @param App\User    $user
     * @param App\Booking $booking
     * @return mixed
     */
    public static function  sendNotificationEmailToUserBookingAccepted(App\User $user, App\Booking $booking)
    {
        return Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.booking_accepted', [
            'user'    => $user,
            'booking' => $booking,
            'date'    => App\Hour::prettifyDate($booking->date),
        ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Booking accepted');
            });
    }

    /**
     * Send a notification email to user that his booking has been declined.
     *
     * @param App\User    $user
     * @param App\Booking $booking
     * @return mixed
     */
    public static function  sendNotificationEmailToUserBookingDeclined(App\User $user, App\Booking $booking)
    {
        return Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.booking_declined', [
            'user'    => $user,
            'booking' => $booking,
            'date'    => App\Hour::prettifyDate($booking->date),
        ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Booking declined');
            });
    }

    public static function sendActivationEmailToUser($token, App\User $user)
    {
        return Mail::later(env('EMAIL_DELAY_TIME', 10), 'emails.activate', [
            'token' => $token,
            'name'  => $user->name,
        ],
            function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject('Account activation');
            });
    }
}
