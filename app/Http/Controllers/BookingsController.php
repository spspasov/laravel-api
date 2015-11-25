<?php

namespace App\Http\Controllers;

use App\Booking;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class BookingsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $bookingId)
    {
        if (!$booking = Booking::find($bookingId)) {
            return response()->json(['not found' => 'booking not found'], 404);
        }
        if ($booking->client->id != $userId) {
            return response()->json(['not authorized' => "you don't have permission to access this resource"], 403);
        }
        return $booking;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $bookingId)
    {
        if (!$booking = Booking::find($bookingId)) {
            return response()->json(['not found' => 'specified booking could not be found'], 404);
        }
        if (!$booking->delete()){
            return response()->json(['error' => 'error on deleting booking'], 400);
        }
        return response()->json(['success' => 'booking successfully deleted'], 200);
    }
}
