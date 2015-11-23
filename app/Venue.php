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
    const CELLAR_DOOR   = 0;

    /*
     * Represents a restaurtant
     */
    const RESTAURANT    = 1;

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
}
