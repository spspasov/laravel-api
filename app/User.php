<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'phone_number'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    /**
     * The active status for this account
     */
    const ACTIVE    = 1;

    /**
     * The inactive status for this account
     */
    const INACTIVE  = 0;

    /**
     * Invoke the boot method in order to cascade
     * and delete child relations (client or bus).
     */
    public static function boot()
    {
        parent::boot();

        static::deleted(function($user) {

           $user->accountable()->delete();
        });
    }
    
    /**
     * Returns the requests that belong to this particular user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function requests()
    {
        return $this->hasMany('App\Request');
    }

    /**
     * Check to see if the passed request belongs
     * belongs to the user
     *
     * @param $requestId
     * @return bool
     */
    public function doesRequestBelongToUser($requestId)
    {
        if (\App\Request::find($requestId)) {
            return \App\Request::find($requestId)->user_id == $this->id ? true : false;
        }

        return false;
    }

    /**
     * Get all of the owning accountable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function accountable()
    {
        return $this->morphTo();
    }

    /**
     * The roles assigned to this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
}
