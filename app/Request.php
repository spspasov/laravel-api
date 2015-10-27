<?php

namespace App;

use App;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'requests';

    /**
     * The attributes that are mass assignable.
     *
     * TODO: Remove pickup and setdown from here and move it to the hidden array
     *
     * @var array
     */
    protected $fillable = ['user_id', 'region_id', 'date', 'passengers', 'lat', 'lon', 'comments', 'status'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * Request has not been yet completed.
     */
    const REQUEST_IS_NOT_COMPLETED = 0;

    /**
     * Request has been completed.
     */
    const REQUEST_HAS_BEEN_COMPLETED = 1;

    /**
     * Does it belong to the user?
     *
     * @param $userId
     * @return bool
     */
    public function belongsToUser($clientId)
    {
        return $this->client_id == $clientId ? true : false;
    }

    /**
     * Returns the client this request belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo('App\Client');
    }

    /**
     * Return all quotes buses have made for this request.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quotes()
    {
        return $this->hasMany('App\Quote');
    }

    /**
     * The region this request has been added to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Region');
    }


    /**
     * Check if the request belongs to the regions
     * the supplied bus is subscribed to
     *
     * @param $busId
     */
    public function belongsToBusRegions($busId)
    {
        $regions = App\Bus::find($busId)->regions;

        return $this->belongsToRegions($regions) ? true : false;
    }

    /**
     * Check if the request belongs to
     * the passed array of regions
     *
     * @param $regions
     * @return bool
     */
    public function belongsToRegions($regions)
    {
        foreach($regions as $region) {
            if ($region) {

                return $this->region_id == $region->id ? true : false;
            }
        }

        return false;
    }
}
