<?php

namespace App\Http\Controllers;

use App\Token;
use App\User;
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
        if ( ! $venue = Venue::with('account')->find($id)) {
            return response()->json(['not found' => 'venue not found'], 404);
        }
        $venue['hours'] = $venue->businessHours();

        return $venue;
    }

    /**
     * Update the given resource.
     *
     * @param $id
     */
    public function update($id, Request $request)
    {

        $venue = Venue::find($id);
        $user = $venue->account;

        $updateVenue = $request->only([
            'image_url',
            'logo_url',
            'url',
            'instagram_username',
            'twitter_username',
            'facebook_id',
            'description',
            'accepts_online_bookings',
            'abn',
        ]);

        $updatesUser = $request->only([
            'email',
            'password',
            'name',
            'phone_number',
        ]);

        /*
         * Remove empty array elements
         */
        $updateVenue = array_filter($updateVenue);
        $updatesUser = array_filter($updatesUser);

        $userValidator = $this->userValidator($updatesUser);

        if ($userValidator->fails()) {
            return $userValidator->errors();
        }

        $venueValidator = $this->venueValidator($updateVenue);

        if ($venueValidator->fails()) {
            return $venueValidator->errors();
        }

        $venue->update($updateVenue);
        $user->update($updatesUser);

        return response()->json([
            'msg'   => 'venue updated successfully!',
            'venue' => $venue,
        ]);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function userValidator(array $data)
    {
        return Validator::make($data, [
            'name'         => 'max:255',
            'email'        => 'email|max:255|unique:users',
            'password'     => 'min:6',
            'phone_number' => 'min:6|regex:/^([0-9\s\-\+\(\)]*)$/',
        ]);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function venueValidator(array $data)
    {
        return Validator::make($data, [
            'accepts_online_bookings' => 'numeric|between:1,2',
            'abn'                     => 'numeric',
        ]);
    }

    /**
     * Send a claim email to the provided venue.
     *
     * @param $venueId
     */
    public function sendClaim($venueId)
    {
        if ( ! $venue = Venue::find($venueId)) {
            return response()->json([
                'not found' => 'requested venue does not exist',
            ]);
        }
        $userId = $venue->account->id;
        $token = Token::generateAndSaveTokenForUser($userId);

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
            'token'   => $request->only('token')['token']],
            200);
    }
}
