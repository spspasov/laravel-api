<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Region;
use Illuminate\Support\Facades\Mail;
use JWTAuth;

class RegionsController extends Controller
{
    /**
     * Send an email to buses that have subscribed to region
     *
     * @param \App\Request $request
     */
    public static function NotifyBusesSubscribedToRegion(\App\Request $request)
    {
        $region = Region::find($request->region_id);


        foreach($region->buses as $bus) {
            $user = $bus->account;

            $token = JWTAuth::fromUser($user);

            // extract this into it's own method in MailController
            // EmailController::sendAuthEmailToBusWithRequestDetails($bus);

            Mail::send('emails.quote_request', ['bus' => $bus, 'token' => $token, 'request' => $request],
                function($message) use ($bus){
                    $message->to($bus->account->email, $bus->account->name)
                        ->subject('Quote request');
                });
        }
    }
}
