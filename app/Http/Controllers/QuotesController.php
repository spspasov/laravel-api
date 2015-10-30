<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Quote;
use App\User;
use App\Bus;
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
     * Validate and create a new request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $quoteDetails = $request->only(
            'bus_id',
            'request_id',
            'max_passengers',
            'duration',
            'cost',
            'expiry',
            'comments'
        );

        $validator = $this->validator($quoteDetails);

        if ($validator->fails()) {

            return response()->json(['error' => 'validation fail'], 400);
        }

//        return $quoteDetails;

        $quoteFromBus = $this->store($quoteDetails);

        EmailsController::sendNotificationEmailToUserRegardingQuote($quoteFromBus);

        return response()->json(['success' => 'true', 'request' => $quoteFromBus], 201);
    }

    /**
     * Validate if the input data matches our requirements
     *
     * @param array $data
     * @return mixed
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'max_passengers'    => 'required',
            'duration'          => 'required',
            'cost'              => 'required',
            'expiry'            => 'required',
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

        return Quote::create([
            'bus_id'            => $data['bus_id'],
            'request_id'        => $data['request_id'],
            'max_passengers'    => $data['max_passengers'],
            'duration'          => $data['duration'],
            'cost'              => $data['cost'],
            'expiry'            => $data['expiry'],
            'comments'          => $data['comments'],
        ]);
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


    /**
     * Return the specified resource for bus
     *
     * @param $busId
     * @param $requestId
     * @return mixed
     */
    public function showQuoteForBus($busId, $requestId)
    {
        $bus = Bus::find($busId);

        return $bus->getQuoteForRequest($requestId);
    }

    public function getPayQuote($userId, $requestId, $quoteId = null)
    {
        $request = App\Request::find($requestId);

        if ( ! $request->belongsToUser($userId)) {
            return response()->json(['fail' => 'request belongs to a different user'], 403);
        }

        if ($request->hasBeenCompleted()) {
            return response()->json(['fail' => 'request has already been completed'], 409);
        }

        // do some checks on quote!!!

        return view('stripe');
    }

    public function postPayQuote(Input $input)
    {
        $token  = $input->get('stripeToken');
        $user   = \App\User::find(8);

        $user->setBillingCard($token);

        dd($user);
    }
}