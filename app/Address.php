<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'type',
        'suburb',
        'street_number',
        'street_name',
        'postcode',
        'lon',
        'lat'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * This constant shows the address is for a venue.
     */
    const VENUE    = 0;

    /**
     * This constant shows the address is for a pickup location.
     */
    const PICKUP    = 1;

    /**
     * This constant shows the address is for a setdown location.
     */
    const SETDOWN   = 2;

    /**
     * Get all of the owning models (request or winery).
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
