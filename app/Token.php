<?php

namespace App;

use Config;
use Illuminate\Database\Eloquent\Model;
use Validator;

class Token extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'single_use_tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'token'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Generate a new single use token.
     *
     * @return string
     */
    public static function generateToken()
    {
        return hash_hmac('sha256', str_random(40), Config::get('app.key'));
    }

    /**
     * Generate a new single use token and associate it with a user.
     *
     * @param $userId
     * @return static
     */
    public static function generateAndSaveTokenForUser($userId)
    {
        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|numeric|exists:users,id',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        if (self::tokenExistsForUser($userId)) {
            return Token::where('user_id', $userId)->get()->first();
        }

        return Token::create([
            'user_id' => $userId,
            'token'   => self::generateToken(),
        ]);
    }

    /**
     * Return the user this token is associated with.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Check if the provided user has a one time use token set.
     *
     * @param $userId
     * @return bool
     */
    private static function tokenExistsForUser($userId)
    {
        return Token::where('user_id', $userId)->get()->first() ? true : false;
    }

    /**
     * Fetch and return a user if it exists by a given single use token.
     *
     * @param $token
     * @return null
     */
    public static function fetchUserByToken($token)
    {
        $token = Token::where('token', $token)->get();

        if ( ! $token->first()) {
            return null;
        }
        $token = Token::find($token[0]->id);
        $user = User::find($token->user_id);

        $token->delete();

        return $user;
    }
}
