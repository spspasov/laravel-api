<?php

namespace App\Http\Controllers;

use App\Token;
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

        if ( ! $region = Region::find($id)) {
            return response()->json(['not found' => 'specified region was not found'], 404);
        }
        if ( ! $region->venues->first()) {
            return response()->json(['not found' => 'specified region does not have any venues'], 404);
        }

        switch ($type) {
            case Venue::CELLAR_DOOR:
                $venues = Venue::with(['account', 'address'])
                    ->where('region_id', $id)
                    ->where('type', Venue::CELLAR_DOOR)
                    ->get();

                if ($venues->first()) {
                    return array_flatten($venues);
                }

                return response()->json([
                    'not found' => 'specified region does not have any venues of the specified type'
                ], 404);
                break;

            case Venue::RESTAURANT:

                $venues = Venue::with(['account', 'address'])
                    ->where('region_id', $id)
                    ->where('type', Venue::RESTAURANT)
                    ->get();

                if ($venues->first()) {
                    return array_flatten($venues);
                }

                return response()->json([
                    'not found' => 'specified region does not have any venues of the specified type'
                ], 404);
                break;

            default:

                return Venue::with(['account', 'address'])->where('region_id', $id)->get();
                break;
            }
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

            $token = Token::generateAndSaveTokenForUser($user->id)['token'];

            EmailsController::sendAuthEmailToBusWithRequestDetails($bus, $token, $request);

        }
    }
}
