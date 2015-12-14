<?php

namespace App\Http\Controllers;

use App\Hour;
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
        if ( ! $venue = Venue::with(['address', 'account'])->find($id)) {
            return response()->json(['not found' => 'venue not found'], 404);
        }
        $venue['name'] = $venue->account->name;
        $venue['email'] = $venue->account->email;
        $venue['phone_number'] = $venue->account->phone_number;
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

        $updatesHours = $request->only([
            'monday_open',
            'monday_close',
            'monday_closed',
            'tuesday_open',
            'tuesday_close',
            'tuesday_closed',
            'wednesday_open',
            'wednesday_close',
            'wednesday_closed',
            'thursday_open',
            'thursday_close',
            'thursday_closed',
            'friday_open',
            'friday_close',
            'friday_closed',
            'saturday_open',
            'saturday_close',
            'saturday_closed',
            'sunday_open',
            'sunday_close',
            'sunday_closed',
        ]);

        /*
         * Remove empty array elements
         */
        $updateVenue = array_filter($updateVenue);
        $updatesUser = array_filter($updatesUser);
        $updatesHours = array_filter($updatesHours);

        $userValidator = $this->userValidator($updatesUser);

        if ($userValidator->fails()) {
            return $userValidator->errors();
        }

        $venueValidator = $this->venueValidator($updateVenue);

        if ($venueValidator->fails()) {
            return $venueValidator->errors();
        }

        $hoursValidator = $this->hoursValidation($updatesHours);

        if ($hoursValidator->fails()) {
            return $hoursValidator->errors();
        }

        if (array_key_exists('password', $updatesUser)) $updatesUser['password'] = bcrypt($updatesUser['password']);

        /*
         * We grab the hour class
        * to use reflection on it further down in the class.
         */
        $hourReference = new \ReflectionClass('App\Hour');
        $constants = $hourReference->getConstants();

        $hoursForUpdating = [];

        foreach ($updatesHours as $day => $hour) {
            // returns ['monday', 'open']
            $day_action = explode('_', $day);

            $day = strtoupper($day_action[0]);
            $action = $day_action[1];


            $hoursForUpdating[$day]['day'] = Hour::where('venue_id', $venue->id)
                ->where('day_of_week', $constants[$day])
                ->get();

            $hoursForUpdating[$day]['action'] = $action;
            $hoursForUpdating[$day]['value'] = $hour;

            $hour = Hour::find($hoursForUpdating[$day]['day'][0]->id);
            $action = $hoursForUpdating[$day]['action'];

            if ($action == "close") {
                $hour->close_time = $hoursForUpdating[$day]['value'];
                $hour->closed = Hour::OPEN;
            } elseif ($action == "open") {
                $hour->open_time = $hoursForUpdating[$day]['value'];
                $hour->closed = Hour::OPEN;
            } elseif ($action == "closed") {
                $hour->closed = Hour::CLOSED;
            }
            $hour->save();
        }

        $venue->update($updateVenue);
        $user->update($updatesUser);

        return response()->json([
            'msg'   => 'venue updated successfully!',
            'venue' => $venue,
            'business hours' => $venue->businessHours()
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
     * Validate if the provided open and close times
     * match the validation rules.
     *
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function hoursValidation(array $data)
    {
        return Validator::make($data, [
            'monday_open'     => 'date_format:H:i',
            'monday_close'    => 'date_format:H:i',
            'tuesday_open'    => 'date_format:H:i',
            'tuesday_close'   => 'date_format:H:i',
            'wednesday_open'  => 'date_format:H:i',
            'wednesday_close' => 'date_format:H:i',
            'thursday_open'   => 'date_format:H:i',
            'thursday_close'  => 'date_format:H:i',
            'friday_open'     => 'date_format:H:i',
            'friday_close'    => 'date_format:H:i',
            'saturday_open'   => 'date_format:H:i',
            'saturday_close'  => 'date_format:H:i',
            'sunday_open'     => 'date_format:H:i',
            'sunday_close'    => 'date_format:H:i',
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
