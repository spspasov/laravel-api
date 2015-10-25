<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'regions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'image_url', 'state', 'lat', 'lon'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * All the buses that have subscribed to this region.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function buses()
    {
        return $this->belongsToMany('App\Bus');
    }

    /**
     * The requests that have been assigned to this region.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany('App\Request');
    }
}
