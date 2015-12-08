<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Venue extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'venues';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'image_url',
        'logo_url',
        'url',
        'instagram_username',
        'twitter_username',
        'facebook_id',
        'description',
        'accepts_online_bookings',
        'abn',
        'region_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['account'];

    /*
     * Represents a cellar door
     */
    const CELLAR_DOOR = 1;

    /*
     * Represents a restaurtant
     */
    const RESTAURANT = 2;

    const DAYS_OF_WEEK = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    ];

    /**
     * Get the account corresponding to the venue.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function account()
    {
        return $this->morphOne('App\User', 'accountable');
    }

    /**
     * The hours this venue has.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hours()
    {
        return $this->hasMany('App\Hour');
    }

    /**
     * The region the venue belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Region');
    }

    /**
     * Return all bookings made to the venue
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Booking');
    }

    /**
     * Return the venue address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function address()
    {
        return $this->morphOne('App\Address', 'addressable');
    }

    /**
     * Returns the opening and closing hours for the venue
     *
     * @return array
     */
    public function businessHours()
    {
        $hours = \DB::table('hours')
            ->where('venue_id', $this->id)
            ->whereIn('closed', [Hour::OPEN, Hour::CLOSED])
            ->get();

        return Hour::convertToArray($hours);
    }

    /**
     * Returns the days the venue is not working outside of the regular working hours.
     *
     * This includes national holidays, as well as custom dates set by the venue,
     * like birthdays.
     * @return array
     */
    public function specialNonWorkingDays()
    {
        return Hour::convertToArray($this->hours->where('closed', Hour::SPECIAL_NON_WORKING_DAY));
    }

    /**
     * Returns the days the venue is open,
     * but with reduced working hours.
     *
     * For example, the venue may be open on Christmas Day, but only till noon.
     *
     * @return array
     */
    public function daysWithReducedWorkingHours()
    {
        return Hour::convertToArray($this->hours->where('closed', Hour::OPEN_WITH_REDUCED_WORKING_HOURS));
    }
}
