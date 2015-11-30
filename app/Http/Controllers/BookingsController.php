<?php

namespace App\Http\Controllers;

use App\Booking;
use App\Hour;
use App\Token;
use App\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class BookingsController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($userId, $bookingId, Request $request)
    {
        if ( ! $booking = Booking::find($bookingId)) {
            return response()->json(['not found' => 'booking not found'], 404);
        }
        if ($booking->client->id != $userId) {
            return response()->json(['not authorized' => "you don't have permission to access this resource"], 403);
        }

        $from = $request->only('from');
        $to = $request->only('to');

        $dates = [$from, $to];

        $validator = $this->dateValidator($request->only(['from', 'to']));

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $dates = Hour::createDateFilters($dates);

        if ( ! Booking::whereBetween('date', [$dates])->get()->first()) {
            return response()->json(['not found' => 'no bookings found for given dates'], 404);
        }

        return Booking::whereBetween('date', [$dates])->get();
    }

    /**
     * Validate and create a new booking.
     *
     * @param array $data
     * @return mixed
     */
    public function create($venueId, Request $request)
    {
        $bookingDetails = $request->only(
            'client_id',
            'request_id',
            'date',
            'comments',
            'pax'
        );
        $bookingDetails['venue_id'] = $venueId;

        if ( ! $venue = Venue::find($venueId)) {
            return response()->json(['not found' => 'requested venue does not exist'], 404);
        }

        if ( ! $venue->accepts_online_bookings) {
            return response()->json(['error' => 'requested venue does not accept online bookings'], 400);

        }
        if ( ! $venue->account->active) {
            return response()->json(['error' => 'requested venue has not been activated yet'], 400);
        }

        $validator = $this->validator($bookingDetails);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $booking = $this->store($bookingDetails);
        $token = Token::generateAndSaveTokenForUser($venue->account->id);
        EmailsController::sendNotificationEmailToVenueBookingMade($venue, $booking, $token);
        return response()->json(['success' => 'true', 'booking' => $bookingDetails], 201);
    }

    /**
     * Validate if the input data matches our requirements.
     *
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'client_id'  => 'required|numeric|exists:clients,id',
            'request_id' => 'numeric|exists:requests,id',
            'date'       => 'required|date_format:"d/m/y"|after:today',
            'pax'        => 'required',
        ]);
    }

    /**
     * Validate if the provided date matches our requirements.
     *
     * @param array $data
     * @return mixed
     */
    protected function dateValidator(array $data)
    {
        return Validator::make($data, [
            'from' => 'date_format:"d/m/y"',
            'to'   => 'date_format:"d/m/y"|after:from',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(array $data)
    {
        return Booking::create([
            'client_id'  => $data['client_id'],
            'venue_id'   => $data['venue_id'],
            'request_id' => $data['request_id'],
            'date'       => $data['date'],
            'comments'   => $data['comments'],
            'pax'        => $data['pax'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($userId, $bookingId)
    {
        if ( ! $booking = Booking::find($bookingId)) {
            return response()->json(['not found' => 'specified booking could not be found'], 404);
        }
        if ( ! $booking->delete()) {
            return response()->json(['error' => 'error on deleting booking'], 400);
        }
        EmailsController::sendNotificationEmailToVenueBookingCancelled($booking);

        return response()->json(['success' => 'booking successfully deleted'], 200);
    }
}
