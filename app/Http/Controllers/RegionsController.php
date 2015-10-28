<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Region;
use Illuminate\Support\Facades\Mail;

class RegionsController extends Controller
{
    public static function NotifyBusesSubscribedToRegion($regionId)
    {
        $region = Region::find($regionId);

        foreach($region->buses as $bus) {

            // extract this into it's own method in MailController
            // EmailController::sendAuthEmailToBusWithRequestDetails($bus->id);

            Mail::send('emails.quote_request', ['bus' => $bus, 'name' => $bus->account->name], function($message) use ($bus){
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Quote request');
            });
        }
    }
}
