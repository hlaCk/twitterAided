<?php

namespace App\Models\User;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Follower extends Model
{
	protected
		$fillable = [
			'user_id',
			'name',
			'screen_name',
			'location',
			'description',
			'url',
			'profile_image_url_https',
			'profile_image_url',
			
			'following',
			'follow_request_sent',
			'notifications',
			'verified',
			'protected',
			'suspended',
			'needs_phone_verification',
			
			'creation_date',
		
			'followers_count',
			'friends_count',
			'listed_count',
			'favourites_count',
			'statuses_count',
		],
		$dates = [
			'creation_date',
		],
		$casts = [
			'following'                => 'bool',
			'follow_request_sent'      => 'bool',
			'notifications'            => 'bool',
			'verified'                 => 'bool',
			'protected'                => 'bool',
			'suspended'                => 'bool',
			'needs_phone_verification' => 'bool',
		];
	
	public function user() {
		return $this->belongsTo(\App\User::class);
	}
	
	
	public function handleNewFollower(Collection $follower) {
		d(
			object
		);
		//rowCallback
	}
}
