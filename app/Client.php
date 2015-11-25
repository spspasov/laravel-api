<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'clients';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ip_address', 'device', 'device_token'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the account corresponding to the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function account()
    {
        return $this->morphOne('App\User', 'accountable');
    }

    /**
     * The requests that return to this client
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany('App\Request');
    }

    /**
     * Return the bookings this user has made
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Booking');
    }
}
