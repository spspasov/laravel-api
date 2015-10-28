<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Quote;
use App;

class QuotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($busId = null)
    {

        if ($busId) {

            return Quote::whereBusId($busId)->get();
        }

        return Quote::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {

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
     * Display the specific quote for the user
     * if it matches the criteria
     *
     * @param $userId
     * @param $requestId
     * @param $quoteId
     * @return \Illuminate\Http\JsonResponse
     */
    public function showQuoteForUser($userId, $requestId, $quoteId)
    {
        if( ! $quote = Quote::find($quoteId)) {

            return response()->json(['not found' => 'No match for quote with id: ' . $quoteId], 404);
        }

        if( ! $request = App\Request::find($requestId)) {

            return response()->json(['not found' => 'No match for request with id: ' . $requestId], 404);
        }

        if($quote->belongsToRequest($requestId)) {
            if($request->belongsToUser($userId)) {

                return Quote::find($quoteId);
            }

            return response()->json(['forbidden' => 'You do not have permission to access this resource'], 403);
        }

        return response()->json(['forbidden' => 'You do not have permission to access this resource'], 403);
    }
}
