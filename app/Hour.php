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

    /**
     * Return a prettified representation of the data passed
     *
     * @param $data
     * @return array
     */
    public static function convertToArray($data)
    {
        $hours = $data;
        $array = [];

        foreach ($hours as $hour) {
            if ($hour->closed == Hour::CLOSED) {
                $array[Venue::DAYS_OF_WEEK[$hour->day_of_week]] = "closed";
            } else if ($hour->closed == Hour::OPEN) {
                $array[Venue::DAYS_OF_WEEK[$hour->day_of_week]] =
                self::prettifyTime($hour->open_time) .
                "-" .
                self::prettifyTime($hour->close_time);
            } else if ($hour->closed == Hour::SPECIAL_NON_WORKING_DAY) {
                $array[$hour->description] = self::prettifyDate($hour->date);
            } else if ($hour->closed == Hour::OPEN_WITH_REDUCED_WORKING_HOURS) {
                $array[$hour->description] = [
                    self::prettifyDate($hour->date) =>
                        self::prettifyTime($hour->open_time) .
                        "-" .
                        self::prettifyTime($hour->close_time)
                ];
            }
        }
        return $array;
    }

    /**
     * Returns a pretty version of the provided date
     *
     * The returned date looks like this:
     *
     * December 31
     *
     * @param $date
     * @return bool|string
     */
    public static function prettifyDate($date)
    {
        return date('F d', strtotime($date));
    }

    /**
     * Returns a pretty version of the provided time
     *
     * @param $time
     * @return bool|string
     */
    public static function prettifyTime($time)
    {
        return date('g:ia', strtotime($time));
    }
}