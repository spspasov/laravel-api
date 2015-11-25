<?php

namespace App\Http\Controllers;

use App\Venue;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

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
        if ($venue = Venue::find($id)) {
            return $venue;
        }
        return response()->json(['not found' => 'venue not found'], 404);
    }
}
