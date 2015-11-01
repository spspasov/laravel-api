<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RegionsController;
use Illuminate\Http\Request;
use App\Http\Requests;
use App;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\AuthenticateController as Auth;
use Illuminate\Support\Facades\Mail;

class RequestsController extends Controller
{
    /**
     * Protect the methods that require authentication
     */
    public function __construct() {

        $this->middleware('jwt.auth', ['except' => ['index', 'show', 'destroy', 'edit', 'create', 'quotes', 'showRequestFromSameRegionAsBus']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return App\Request::all();
    }

    /**
     * We can send these using the url and the following syntax:
     *
     * http://localhost:8000/api/request/create?user_id=1&region_id=1...
     *
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        /*
         * Request details
         */
        $requestDetails = $request->only(
            'region_id',
            'date',
            'passengers',
            'comments'
         );
        $userId = ['user_id' => AuthenticateController::getAuthenticatedUser()->id];
        $requestDetails = array_merge($requestDetails, $userId);
        $requestValidator = $this->requestValidator($requestDetails);

        if ($requestValidator->fails()) {
            return response()->json(['error' => $requestValidator->errors()], 400);
        }
        $requestFromUser = $this->store($requestDetails);

        /*
         * Pickup address details
         */
        $pickUpAddressDetails = $request->only(
            'pickup_type',
            'pickup_suburb',
            'pickup_street_number',
            'pickup_street_name',
            'pickup_postcode'
        );
        $pickUpAddressValidator = $this->pickupAddressValidator($pickUpAddressDetails);

        if ($pickUpAddressValidator->fails()) {
            return response()->json(['error' => $pickUpAddressValidator->errors()], 400);
        }
        $pickUpAddress = $this->storePickupAddress($pickUpAddressDetails);

        /*
         * Setdown address details
         */
        $setdownAddressDetails = $request->only(
            'setdown_type',
            'setdown_suburb',
            'setdown_street_number',
            'setdown_street_name',
            'setdown_postcode'
        );
        $setdownAddressValidator = $this->setdownAddressValidator($setdownAddressDetails);

        if ($setdownAddressValidator->fails()) {
            return response()->json(['error' => $setdownAddressValidator->errors()], 400);
        }
        $setdownAddress = $this->storeSetdownAddress($setdownAddressDetails);

        /*
         * Add the needed relationships
         */
        $requestFromUser->addresses()->save($pickUpAddress);
        $requestFromUser->addresses()->save($setdownAddress);

        /*
         * Send relevant email
         */
        RegionsController::NotifyBusesSubscribedToRegion($requestFromUser);

        return response()->json([
            'success'           => 'true',
            'request'           => $requestFromUser,
            'pickup_address'    => $pickUpAddress,
            'setdown_address'   => $setdownAddress
        ], 201);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function requestValidator(array $data)
    {

        /*
         * The date must be in the following format
         *
         * yyyy-mm-dd
         */
        return Validator::make($data, [
            'user_id'           => 'required',
            'region_id'         => 'required|exists:regions,id',
            'date'              => 'required|date|after:today',
            'passengers'        => 'required|numeric|max:30',
            'comments'          => 'required',
        ]);
    }

    /**
     * Validate if the provided address details
     * match the validation rules
     *
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function pickupAddressValidator(array $data)
    {
        return Validator::make($data, [
            'pickup_suburb'         => 'required',
            'pickup_street_number'  => 'required',
            'pickup_street_name'    => 'required',
            'pickup_postcode'       => 'required|numeric'
        ]);
    }

    /**
     * Validate if the provided address details
     * match the validation rules
     *
     * @param array $data
     * @return \Illuminate\Validation\Validator
     */
    protected function setdownAddressValidator(array $data)
    {
        return Validator::make($data, [
            'setdown_suburb'         => 'required',
            'setdown_street_number'  => 'required',
            'setdown_street_name'    => 'required',
            'setdown_postcode'       => 'required|numeric'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $data
     * @return mixed
     */
    protected function store(array $data)
    {
        return App\Request::create([
            'user_id'           => $data['user_id'],
            'region_id'         => $data['region_id'],
            'date'              => $data['date'],
            'passengers'        => $data['passengers'],
            'comments'          => $data['comments'],
        ]);
    }

    /**
     * Persist the pickup address in storage
     *
     * @param array $data
     * @return static
     */
    protected function storePickupAddress(array $data)
    {
        return App\Address::create([
            'type'           => App\Address::PICKUP,
            'suburb'         => $data['pickup_suburb'],
            'street_number'  => $data['pickup_street_number'],
            'street_name'    => $data['pickup_street_name'],
            'postcode'       => $data['pickup_postcode'],
        ]);
    }

    /**
     * Persist the setdown address in storage
     *
     * @param array $data
     * @return static
     */
    protected function storeSetdownAddress(array $data)
    {
        return App\Address::create([
            'type'           => App\Address::SETDOWN,
            'suburb'         => $data['setdown_suburb'],
            'street_number'  => $data['setdown_street_number'],
            'street_name'    => $data['setdown_street_name'],
            'postcode'       => $data['setdown_postcode'],
        ]);
    }

    /**
     * @param $id
     * @param null $secondId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id, $secondId = null)
    {
        if ($secondId) {
            if (App\Request::find($secondId)) {
                return App\Request::find($secondId);
            }
            return response()->json(['not found' => 'No match for request with id: ' . $secondId], 404);
        }
        if (App\Request::find($id)) {
            return App\Request::find($id);
        }
        return response()->json(['not found' => 'No match for request with id: ' . $id], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $request = App\Request::find($id);

        if ($request->delete($id)) {

            return response()->json(['success' => 'Request with id of: ' . $id . " successfully deleted."], 200);
            } else {

            return response()->json(['failed to delete resource'], 400);
        }
    }

    /**
     * Returns the quotes given by buses to the given request
     *
     * @param null $userId
     * @param $requestId
     * @return mixed
     */
    public function quotes($userId = null, $requestId)
    {
        return App\Request::find($requestId)->quotes;
    }

    /**
     * Show the request only if it matches
     * with the regions that the bus has subscribed to.
     *
     * @param $busId
     * @param $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showRequestFromSameRegionAsBus($requestId, $busId, $token = null)
    {
        if (App\Request::find($requestId)) {
            if (App\Request::find($requestId)->belongsToBusRegions($busId)) {

                return response()->json([
                    'request'   => App\Request::find($requestId),
                    'token'     => $token],
                    200
                );
            }

            return response()->json(['forbidden' => 'You do not have permission to view this resource.'], 403);
        }

        return response()->json(['not found' => 'No match for request with id: ' . $requestId], 404);
    }
}