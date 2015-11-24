<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hour extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'hours';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'venue_id',
        'day_of_week',
        'open_time',
        'close_time',
        'closed',
        'date',
        'description',
    ];

    /*
     * The venue is open on this day
     */
    const OPEN = 0;

    /*
     * The venue is open, but with different time than usual
     */
    const OPEN_WITH_REDUCED_WORKING_HOURS = 1;

    /*
     * The venue is closed
     */
    const CLOSED = 2;

    /*
     * A national holiday or a custom defined day off
     */
    const SPECIAL_NON_WORKING_DAY = 3;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The venue this hour is associated with
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function venue()
    {
        return $this->belongsTo('App\Venue');
    }
}