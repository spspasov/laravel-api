<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Region;

class RegionsController extends Controller
{
    public static function NotifyBusesSubscribedToRegion($regionId)
    {
        $region = Region::find($regionId);

        foreach($region->buses as $bus) {

            // EmailController::sendAuthEmailToBusWithRequestDetails($busId);

            return $bus;
        }
    }
}
