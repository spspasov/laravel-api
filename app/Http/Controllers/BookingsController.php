<?php

namespace App\Http\Controllers;

use App\Booking;
use Doctrine\DBAL\Types\IntegerType;
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
     * Validate and create a new booking.
     *  TODO: only on venues which accept_online_booking & active
     *  TODO: email sent to venue, with link to authenticate them ­ see single use token below
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

        $validator = $this->validator($bookingDetails);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $this->store($bookingDetails);
        // send email
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
     * @param  int $id
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
        if (!$booking = Booking::find($bookingId)) {
            return response()->json(['not found' => 'specified booking could not be found'], 404);
        }
        if (!$booking->delete()) {
            return response()->json(['error' => 'error on deleting booking'], 400);
        }
        EmailsController::sendNotificationEmailToVenueBookingCancelled($booking);

        return response()->json(['success' => 'booking successfully deleted'], 200);
    }
}