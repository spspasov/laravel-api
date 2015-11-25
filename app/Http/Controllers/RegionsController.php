<?php

namespace App\Http\Controllers;

use App\Venue;
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
     * Show the specified resource
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        if ($region = Region::find($id)) {
            return $region;
        }
        return response()->json(['not found' => 'the requested region does not exist'], 404);
    }

    /**
     * Return all the venues associated with the given region
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function venues($id, Request $request)
    {
        $type = $request->only('type')['type'];

        if ($region = Region::find($id)) {
            if ($region->venues->first()) {
                switch ($type) {
                    case Venue::CELLAR_DOOR:
                        $venues = $region->venues->where('type', Venue::CELLAR_DOOR);
                        if ($venues->first()) {
                            return $venues;
                        }
                        return response()->json([
                            'not found' => 'specified region does not have any venues of the specified type'
                        ], 404);
                        break;
                    case Venue::RESTAURANT:
                        $venues = $region->venues->where('type', Venue::RESTAURANT);
                        if ($venues->first()) {
                            return $venues;
                        }
                        return response()->json([
                            'not found' => 'specified region does not have any venues of the specified type'
                        ], 404);
                        break;
                    default:
                        return $region->venues;
                        break;
                }
            }
            return response()->json(['not found' => 'specified region does not have any venues'], 404);
        }
        return response()->json(['not found' => 'specified region was not found'], 404);
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
