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
    protected $fillable = [ 'user_id',
                            'region_id',
                            'date',
                            'passengers',
                            'pickup_lat',
                            'pickup_lon',
                            'setdown_lat',
                            'setdown_lon',
                            'comments',
                            'status'
                            ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * Request has not been yet completed.
     */
    const REQUEST_HAS_NOT_BEEN_COMPLETED = 0;

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
    public function belongsToUser($userId)
    {
        return $this->user_id == $userId ? true : false;
    }

    /**
     * Returns the user this request belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
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
     * Get the addresses associated with the current request
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function addresses()
    {
        return $this->morphMany('App\Address', 'addressable');
    }

    /**
     * Return the pickup address of the request
     *
     * @return mixed
     */
    public function pickup()
    {
        return $this->addresses()->where('type', App\Address::PICKUP);
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

    /**
     * Checks to see if the current request
     * has been completed
     *
     * @return bool
     */
    public function hasBeenCompleted()
    {
        return $this->status == App\Request::REQUEST_HAS_BEEN_COMPLETED ?: false;
    }

    /**
     * Mark the status of the request as completed
     */
    public function complete() {
        $this->status = App\Request::REQUEST_HAS_BEEN_COMPLETED;
        return $this->save();
    }
}
