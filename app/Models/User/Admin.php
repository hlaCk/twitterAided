<?php

namespace App\Models\User;

use App\Models\Auth\Authenticatable;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
	protected $fillable = [
		'user_id',
		'user_name',
		'admin'
	];
	
	public function user() {
		return $this->belongsTo(User::class);
	}
	
	public function scopeByUser($q, $user) {
		if($user instanceof Authenticatable) {
			$user = $user->id;
		} else if(intval($user)){
			$user = intval($user);
		} else {
			$user = '999999';
		}
		
		return $q->where('user_id', $user);
	}
	
	public static function getByUser($user) {
		if($user instanceof Authenticatable) {
			$user = $user->id;
		} else if(intval($user)){
			$user = intval($user);
		} else {
			$user = '999999';
		}
		
		return self::byUser($user)->first();
	}
}
