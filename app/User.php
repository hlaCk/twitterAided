<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Auth\Authenticatable as Authenticatables;
use Laratrust\Traits\LaratrustUserTrait;


class User extends Authenticatables
{
    use Notifiable;
	use LaratrustUserTrait; // add this trait to your user model
	
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
	
		't_id',
		't_name',
		't_screen_name',
	
		't_oauth_token',
		't_oauth_token_secret',
		't_followers_count',
		't_friends_count',
	
		'status',
		'token_key',
		'last_update',
    ];
	/*
	public function getTwitterAttribute($value) {
		return collect(fractal($this, function ($user) {
			$columns = array_flip(self::AT_COLUMNS_MAP);
			
			$data = collect($user->attributes)
				->mapWithKeys(function ($v, $i = null) use ($columns) {
					if ($i && isset($columns[$i])) {
						return [
							$columns[$i] => $v
						];
					}
					
					return [];
				});
			
			return $data->toArray();
		})->toArray());
	}*/
}
