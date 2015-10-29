<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public static function sendAuthEmailToBusWithRequestDetails($bus, $token, $request)
    {
        Mail::send('emails.quote_request', ['bus' => $bus, 'token' => $token, 'request' => $request],
            function($message) use ($bus){
                $message->to($bus->account->email, $bus->account->name)
                    ->subject('Quote request');
            });
    }
}
