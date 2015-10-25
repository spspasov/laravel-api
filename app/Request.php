<?php

namespace App;

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
}
