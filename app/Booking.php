<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'venue_id',
        'request_id',
        'date',
        'status',
        'comments',
        'pax',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /*
     * The booking has not been processed by the venue yet.
     */
    const PENDING   = 0;

    /*
     * The booking has been accepted.
     */
    const ACCEPTED  = 1;

    /*
     * The booking has been declined.
     */
    const DECLINED  = 2;

    /**
     * Return the specified fields as Carbon\Carbon instances.
     *
     * @return array
     */
    public function getDates() {
        return [
            'created_at',
            'updated_at',
            'date',
        ];
    }

    /**
     * Return the user that made the booking.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    /**
     * Return the venue this booking was made to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function venue()
    {
        return $this->belongsTo('App\Venue');
    }

    /**
     * Return the request this booking is optionally attached to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo('App\Request');
    }

    /**
     * Return true if the booking has been accpeted.
     *
     * @return bool
     */
    public function isAccepted()
    {
        return $this->status === $this::ACCEPTED;
    }

    /**
     * Return true if the booking has been declined.
     *
     * @return bool
     */
    public function isDeclined()
    {
        return $this->status === $this::DECLINED;
    }

    /**
     * Return true if the booking is still pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === $this::PENDING;
    }

    /**
     * Set the status of the request to accepted.
     *
     * @return bool
     */
    public function accept()
    {
        $this->status = $this::ACCEPTED;

        return $this->save();
    }

    /**
     * Set the status of the request to declined.
     *
     * @return bool
     */
    public function decline()
    {
        $this->status = $this::DECLINED;

        return $this->save();
    }
}
