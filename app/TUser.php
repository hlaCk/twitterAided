<?php

namespace App;

use App\Models\HT;
use App\Utilities\Twitter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use League\Fractal\Serializer\ArraySerializer;
use App\Models\Auth\Authenticatable as Authenticatables;

class TUser extends Authenticatables
{
	protected $fillable = [
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
	public function getUserAttribute($value) {
		return collect(fractal($this, function ($user) {
			$columns = self::AT_COLUMNS_MAP;
			
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
	
	public function getAttribute($value) {
		$attrs = collect($this->attributes);
		if($attrs->has("t_{$value}") || $attrs->has($value))
			return $attrs->get("t_{$value}", $attrs->get($value));
		else
			return parent::getAttribute($value);
		
	}
}
