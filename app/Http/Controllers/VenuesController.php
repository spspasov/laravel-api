<?php

namespace App\Http\Controllers;

use App\Token;
use App\Venue;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VenuesController extends Controller
{
    /**
     * Show the specified resource
     *
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        if ( ! $venue = Venue::find($id)) {
            return response()->json(['not found' => 'venue not found'], 404);
        }
        return $venue;
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    public function create(Request $request)
    {

    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(array $data)
    {

    }

    /**
     * Send a claim email to the provided venue.
     *
* @param $venueId
*/
    public function sendClaim($venueId)
    {
        $token = Token::generateAndSaveTokenForUser($venueId);
        $venue = Venue::find($venueId);

        if ( ! $email = EmailsController::sendClaimEmailToVenue($venue, $token)) {
            return response()->json(['error' => 'Email not sent'], 400);
        }
        return response()->json(['success' => 'Email sent successfully!'], 200);
    }

    /**
     * Dummy method for claiming a venue.
     *
     * @param Request $request
     * @return array
     */
    public function claimVenue(Request $request)
    {
        return response()->json([
            'success' => 'venue claimed successfully',
            'token' => $request->only('token')['token']],
            200);
    }
}
