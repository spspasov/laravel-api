<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Validator;
use Illuminate\Support\Facades\Input;

class RequestsController extends Controller
{
    /**
     * Protect the methods that require authentication
     */
    public function __construct() {

        $this->middleware('jwt.auth', ['except' => ['index', 'show', 'destroy', 'edit', 'create', 'quotes']]);
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
                                         'pickup',
                                         'setdown',
                                         'comments'
                                         );

        $validator = $this->validator($requestDetails);

        if ($validator->fails()) {

            return response()->json(['error' => 'validation fail', 'data' => $requestDetails], 401);
        }

        $this->store($requestDetails);
        /**
         * TODO: Only for development purposes. Delete before going to production
         */
        return response()->json(['success' => 'true', 'request' => $requestDetails], 201);
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
            'passengers'        => 'required|numeric|max:100',
            'pickup'            => 'required|alpha_num',
            'setdown'           => 'required|alpha_num',
            'comments'          => 'alpha_num|max:2000',
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
            'pickup'            => $data['pickup'],
            'setdown'           => $data['setdown'],
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

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
    public function destroy($id)
    {
        $request = App\Request::find($id);

        if ($request->delete($id)) {

            return response()->json(['success' => 'Request with id of: ' . $id . " successfully deleted."], 200);
            } else {

            return response()->json(['failed to delete resource'], 400);
        }
    }

    public function quotes($userId = null, $requestId)
    {
        return App\Request::find($requestId)->quotes;
    }
}
