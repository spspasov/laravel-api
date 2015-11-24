<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Region;
use JWTAuth;

class RegionsController extends Controller
{
    /**
     * Return a listing of the resource
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function index()
    {
        return Region::all();
    }

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

            EmailsController::sendAuthEmailToBusWithRequestDetails($bus, $token, $request);

        }
    }
}
