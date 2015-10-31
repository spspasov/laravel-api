<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Region;
use App\Bus;
class BusesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filters = Input::only('limit');

        if ($filters['limit'] != null) {

            return Bus::all()->take($filters['limit']);
        }

        return Bus::all();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Bus::find($id) ? Bus::find($id) : response()->json(['not found' => 'No match for bus with id: ' . $id], 404);
    }

    /**
     * All of the requests made by users
     * from the same region bus is subscribed to
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function allRequestsForBusFromSameRegions($id)
    {
        if ($bus = Bus::find($id)) {

            return $bus->requests() ? $bus->requests()[0] : response()->json(['not found' => 'No requests found for this region'], 404);
        }

        return response()->json(['not found' => 'No match for bus with id: ' . $id], 404);
    }

    /**
     * Show the specific request that belongs to this bus' region
     *
     * @param $busId
     * @param $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showRequestFromSameRegionAsBus($busId, $requestId)
    {
        $requests = Bus::find($busId)->requests()[0];

        foreach ($requests as $request) {
            if ($request->id == $requestId) {
                return $request;
            }
        }

        return response()->json(['not found' => 'No match for request with id: ' . $requestId], 404);
    }

    /**
     * Shows only the quotes bus has made
     * that have been paid
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function showQuotesWithTransaction($id)
    {
        if ( ! Bus::find($id)->quotesWithTransaction()->first()) {
            return response()->json(['not found' => 'You have no accepted quotes yet.'], 404);
        }

        return Bus::find($id)->quotesWithTransaction();
    }

    /**
     * Subscribe the provided bus to the provided region
     *
     * @param $busId
     * @param $regionId
     * @return mixed
     */
    public function subscribeToRegion($busId, $regionId)
    {
        if (Region::find($regionId)) {
            if ($bus = Bus::find($busId)) {
                if ( ! $bus->isSubscribedToRegion($regionId)) {
                    $bus->subscribeToRegion($regionId);

                    return response()->json(['success' =>
                        'bus with id of ' . $bus->id .
                        ' is now subscribed to region with id of ' . $regionId],
                        200);
                }
                return response()->json(['error' =>
                    'bus with id of: ' . $busId .
                    ' has already subscribed to region with id of: ' . $regionId],
                    409);
            }
            return response()->json(['error' => 'bus with id of: ' . $busId .' not found'], 404);
        }
        return response()->json(['error' => 'region with id of: ' . $regionId .' not found'], 404);
    }

    /**
     * Unsubscribe from a particular region
     *
     * @param $busId
     * @param $regionId
     */
    public function unsubscribeFromRegion($busId, $regionId)
    {
        if (Region::find($regionId)) {
            if ($bus = Bus::find($busId)) {
                if ($bus->isSubscribedToRegion($regionId)) {
                    $bus->unsubscribeFromRegion($regionId);

                    return response()->json(['success' =>
                        'bus with id of ' . $bus->id .
                        ' is now unsubscribed from region with id of ' . $regionId],
                        200);
                }
                return response()->json(['error' =>
                    'bus with id of: ' . $busId .
                    ' is not subscribed to region with id of: ' . $regionId],
                    409);
            }
            return response()->json(['error' => 'bus with id of: ' . $busId .' not found'], 404);
        }
        return response()->json(['error' => 'region with id of: ' . $regionId .' not found'], 404);
    }

    /**
     * List all regions bus has subscribed to
     *
     * @param  $busId
     * @return \Illuminate\Http\JsonResponse
     */
    public function listRegions($busId)
    {
        if ($bus = Bus::find($busId)) {
            if ($bus->regions->first()) {

                return $bus->regions;
            }
            return response()->json(['error' =>
                'bus with id of: ' . $busId .
                ' is not subscribed to any regions'],
                404);
        }
        return response()->json(['error' =>
            'bus with id of: ' . $busId .
            ' not found'],
            404);
    }
}
