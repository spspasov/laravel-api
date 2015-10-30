<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Bus;
use Illuminate\Support\Facades\Input;

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

    public function showQuotesWithTransaction($id)
    {
        if ( ! Bus::find($id)->quotesWithTransaction()->first()) {
            return response()->json(['not found' => 'You have no accepted quotes yet.'], 404);
        }

        return Bus::find($id)->quotesWithTransaction();
    }
}
