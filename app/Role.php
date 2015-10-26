<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The default role given to a client
     */
    const ROLE_CLIENT   = 1;

    /**
     * The role given to businesses (buses)
     */
    const ROLE_BUS      = 2;

    /**
     * The superuser role
     */
    const ROLE_ADMIN    = 3;

    /**
     * The users that are assigned the current role
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    /**
     * The buses associated with the particular role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function buses()
    {
        return $this->belongsToMany('App\Bus');
    }
}
