<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Cashier\Contracts\Billable as BillableContract;
use Laravel\Cashier\Billable as Billable;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract,
                                    BillableContract
{
    use Authenticatable, Authorizable, CanResetPassword, Billable;

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
    protected $hidden = [
        'password',
        'remember_token',
        'stripe_active',
        'stripe_id',
        'stripe_subscription',
        'stripe_plan',
        'last_four',
        'trial_ends_at',
        'subscription_ends_at',
    ];


    /**
     * Instruct Eloquent to return the columns as Carbon / DateTime
     * instances instead of raw strings.
     *
     * @var array
     */
    protected $dates = ['trial_ends_at', 'subscription_ends_at'];

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

    /**
     * Return the bookings this user has made
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bookings()
    {
        return $this->hasMany('App\Booking');
    }

    /**
     * Store the user's active card
     * We can retrieve it later for another charge
     *
     * @param $stripeToken
     */
    public function setBillingCard($stripeToken) {

        if ($this->stripeIsActive()) {
            return $this->updateCard($stripeToken);
        }

        $stripeGateway = $this->subscription();

        $customer = $stripeGateway->createStripeCustomer($stripeToken, [
            'email' => $this->email
        ]);
        return $stripeGateway->updateLocalStripeData($customer);
    }
}
