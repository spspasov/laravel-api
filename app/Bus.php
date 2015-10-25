<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
