<?php

namespace App\Http\Controllers;

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
        if (!$venue = Venue::find($id)) {
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(array $data)
    {

    }
}
