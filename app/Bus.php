<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Bus extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'buses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'image_url', 'phone_number', 'description', 'terms'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * The quotes that this bus has made.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quotes()
    {
        return $this->hasMany('App\Quote');
    }

    /**
     * The regions this bus has subscribed to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function regions()
    {
        return $this->belongsToMany('App\Region');
    }

    /**
     * All the requests that have been made
     * in the regions the bus is subscribed to
     *
     * @return Collection
     */
    public function requests()
    {
        $requests = [];

        foreach($this->regions as $region) {
            array_push($requests, $region->requests);
        }

        return Collection::make(array_flatten($requests)[0]);
    }
}
