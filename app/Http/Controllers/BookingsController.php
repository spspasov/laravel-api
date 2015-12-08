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
    public function show($venueId, $bookingId)
    {
        if ( ! $booking = Booking::find($bookingId)) {
            return response()->json(['error' => 'specified resource could not be found'], 404);
        }
        if ($booking->venue_id != $venueId) {
            return response()->json(['error' => 'you do not have permission to access this resource'], 403);
        }

        return $booking;
    }


    /**
     * Display the specified resources.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showAll($venueId, Request $request)
    {

        $from = $request->only('from');
        $to = $request->only('to');
        $status = $request->only('status')['status'];

        $dates = [$from, $to];

        $validator = $this->filterValidator($request->only(['from', 'to', 'status']));

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()]);
        }

        $dates = Hour::createDateFilters($dates);

        if ( ! Booking::whereBetween('date', [$dates])->get()->first()) {
            return response()->json(['not found' => 'no bookings found for given dates'], 404);
        }

        if ($status) {
            return Booking::whereBetween('date', [$dates])
                ->where('status', $status)
                ->where('venue_id', $venueId)
                ->get();
        }

        return Booking::whereBetween('date', [$dates])->where('venue_id', $venueId)->get();
    }

    /**
     * Validate and create a new booking.
     *
     * @param array $data
     * @return mixed
     */
    public function create($venueId, Request $request)
    {
        $authenticatedClientId = AuthenticateController::getAuthenticatedUser()->accountable->id;

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

        if ( $bookingDetails['client_id'] != $authenticatedClientId) {
            return response()->json(['not authorized' => 'you are not authorized to perform this action'], 403);
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
            'client_id'  => 'required|integer|exists:clients,id',
            'request_id' => 'integer|exists:requests,id',
            'date'       => 'required|date_format:"d/m/y"|after:today',
            'pax'        => 'required|integer',
        ]);
    }

    /**
     * Validate if the provided date matches our requirements.
     *
     * @param array $data
     * @return mixed
     */
    protected function filterValidator(array $data)
    {
        return Validator::make($data, [
            'from'   => 'date_format:"d/m/y"',
            'to'     => 'date_format:"d/m/y"|after:from',
            'status' => 'integer|between:0,2',
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

    public function changeBookingStatus($venueId, $bookingId, Request $request)
    {
        $status = $request->only('status');

        $validator = Validator::make($status, [
            'status' => 'required|integer|between:1,2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 400);
        }

        if ( ! $booking = Booking::find($bookingId)) {
            return response()->json([
                'not found' => 'booking not found',
            ], 404);
        }

        if ($booking->venue_id != $venueId) {
            return response()->json([
                'error' => 'specified booking does not belong to specified venue',
            ], 400);
        }

        if ($status['status'] == Booking::ACCEPTED) {

            if ($booking->isAccepted()) {
                return response()->json([
                    'error' => 'specified booking has already been accepted',
                ], 400);
            }

            if ( ! $booking->accept()) {
                return response()->json([
                    'error' => 'an error occured when updating the resource',
                ], 500);
            }
            EmailsController::sendNotificationEmailToUserBookingAccepted($booking->client->account, $booking);
        } else {

            if ($booking->isDeclined()) {
                return response()->json([
                    'error' => 'specified booking has already been declined',
                ], 400);
            }

            if ( ! $booking->decline()) {
                return response()->json([
                    'error' => 'an error occured when updating the resource',
                ], 500);
            }
            EmailsController::sendNotificationEmailToUserBookingDeclined($booking->client->account, $booking);
        }

        return response()->json([
            'success' => 'booking successfully updated',
            'booking' => $booking,
        ]);
    }
}
