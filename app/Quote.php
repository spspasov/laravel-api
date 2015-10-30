<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

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
    protected $fillable = ['bus_id', 'request_id', 'max_passengers', 'duration', 'cost', 'expiry', 'comments'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Default state - quote will never expire.
     */
    const EXPIRE_NEVER             = 0;

    /**
     * Quote will expire after three days.
     */
    const EXPIRE_AFTER_THREE_DAYS  = 1;

    /**
     * Quote will expire after a week.
     */
    const EXPIRE_AFTER_ONE_WEEK    = 2;

    /**
     * Transaction has not been made for this quote.
     */
    const TRANSACTION_NOT_MADE     = 0;


    /**
     * Transaction has been made.
     */
    const TRANSACTION_MADE         = 1;

    /**
     * Request this quote belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo('App\Request');
    }

    /**
     * Bus that made the quote
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bus()
    {
        return $this->belongsTo('App\Bus');
    }

    /**
     * Does this quote belong to the given request?
     *
     * @param $requestId
     * @return bool
     */
    public function belongsToRequest($requestId)
    {
        return $this->request_id == $requestId ? true : false;
    }

    /**
     * Mark the transaction as payed.
     *
     * @return bool
     */
    public function pay()
    {
        $this->has_transaction = $this::TRANSACTION_MADE;

        return $this->save();
    }

    /**
     * Check if the quote has received a transaction
     *
     * @return bool
     */
    public function hasBeenPaid()
    {
        return $this->has_transaction == $this::TRANSACTION_MADE;
    }

    /**
     * Check if the quote has expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return Carbon::now()->gt($this->dateOfExpiry()) ? true : false;
    }

    /**
     * Returns the date of expiry as a Carbon instance
     *
     * @return Carbon
     */
    public function dateOfExpiry()
    {
        $dateOfExpiry = null;

        switch ($this->expiry):
            case $this::EXPIRE_AFTER_THREE_DAYS:
                return $dateOfExpiry = $this->created_at->addDays(3);
            case $this::EXPIRE_AFTER_ONE_WEEK:
                return $dateOfExpiry = $this->created_at->addWeek();
            default:
                return $dateOfExpiry = $this->created_at->addYears(1000);
        endswitch;
    }
}
