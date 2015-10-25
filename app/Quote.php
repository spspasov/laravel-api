<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'quotes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['max_passengers', 'duration', 'cost', 'expiry', 'comments'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Quote will expire after three days.
     */
    const THREE_DAYS    = 1;

    /**
     * Quote will expire after a week.
     */
    const ONE_WEEK      = 2;

    /**
     * Request this quote belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo('App\Request');
    }

//    public function bus()
//    {
//        return $this->belongsTo('App\Bus');
//    }

    public function belongsToRequest($requestId)
    {
        return $this->request_id == $requestId ? true : false;
    }
}