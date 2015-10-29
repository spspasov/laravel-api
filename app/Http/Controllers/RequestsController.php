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
        $requestDetails = $request->only('user_id',
                                         'region_id',
                                         'date',
                                         'passengers',
                                         'pickup_lon',
                                         'pickup_lat',
                                         'setdown_lon',
                                         'setdown_lat',
                                         'comments'
                                         );

        $validator = $this->validator($requestDetails);

        if ($validator->fails()) {

            return response()->json(['error' => 'validation fail'], 400);
        }

        $requestFromUser = $this->store($requestDetails);

        RegionsController::NotifyBusesSubscribedToRegion($requestFromUser);

        return response()->json(['success' => 'true', 'request' => $requestFromUser], 201);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * TODO: Refine validation rules
     *
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_id'           => 'required',
            'region_id'         => 'required',
            'date'              => 'required',
            'passengers'        => 'required|numeric|max:30',
            'pickup_lon'        => 'required',
            'pickup_lat'        => 'required',
            'setdown_lon'       => 'required',
            'setdown_lat'       => 'required',
            'comments'          => 'required',
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
            'pickup_lon'        => $data['pickup_lon'],
            'pickup_lat'        => $data['pickup_lat'],
            'setdown_lon'       => $data['setdown_lon'],
            'setdown_lat'       => $data['setdown_lat'],
            'comments'          => $data['comments'],
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