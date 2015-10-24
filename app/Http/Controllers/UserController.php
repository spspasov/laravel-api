<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;
use App;
use Illuminate\Support\Facades\Input;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends BaseController
{

    /**
     * Protect the methods that require authentication
     */
    public function __construct() {

        $this->middleware('jwt.auth', ['except' => ['index', 'show', 'requests', 'deleteUncompletedRequest']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $filters = Input::only('limit');

        if ($filters['limit'] != null) {

            return User::all()->take($filters['limit']);
        }

        return User::all();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
            return User::find($id) ? User::find($id) : response()->json(['not found' => 'No match for user with id: ' . $id], 404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Returns all requests by the given user
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function requests($id)
    {
        return User::find($id)->requests;
    }

    /**
     * Delete a request that has not been
     * completed yet that belongs to the user
     *
     * @param $userId
     * @param $requestId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUncompletedRequest($userId, $requestId)
    {
        $user = User::find($userId);
        $request = App\Request::find($requestId);

        if ($user->doesRequestBelongToUser($requestId)) {
            if ($request->status == App\Request::REQUEST_IS_NOT_COMPLETED) {
                if ($request->delete($requestId)) {

                    return response()->json(['success' => 'Request with id of: ' . $requestId . " successfully deleted."], 200);
                } else {

                    return response()->json(['failed to delete resource'], 400);
                }
            }

            return response()->json(["msg" => "failed to delete resource",
                "reason" => "request with id of: " . $requestId .
                " has already been completed"],
                400);
        }

        return response()->json(["msg" => "failed to delete resource",
            "reason" => "request with id of: " . $requestId .
            " doesn't belong to user with id of: " . $userId .
            " or it doesn't exist."],
            400);
    }
}
