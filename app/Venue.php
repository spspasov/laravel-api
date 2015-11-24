<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /*
     * Represents a cellar door
     */
    const CELLAR_DOOR = 0;

    /*
     * Represents a restaurtant
     */
    const RESTAURANT = 1;

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
     * The hours this venue has
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function hours()
    {
        return $this->hasMany('App\Hour');
    }

    /**
     * Return the opening and closing hours for the venue
     *
     * @return array
     */
    public function businessHours()
    {
        $hours = $this->hours;
        $business_hours = [];

        foreach ($hours as $hour) {
            if ($hour->closed == Hour::CLOSED) {
                $business_hours[Venue::DAYS_OF_WEEK[$hour->day_of_week]] = "closed";
            } else {
                $business_hours[Venue::DAYS_OF_WEEK[$hour->day_of_week]] = $hour->open_time . "-" . $hour->close_time;
            }
        }
        return $business_hours;
    }

    public function specialNonWorkingDays()
    {
        return $this->hours->where('closed', Hour::SPECIAL_NON_WORKING_DAY);
    }

    public function daysWithReducedWorkingHours()
    {
        return $this->hours->where('closed', Hour::OPEN_WITH_REDUCED_WORKING_HOURS);
    }
}
