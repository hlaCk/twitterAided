<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
	protected $fillable = [
		'user_id',
		'location',
		'description',
		'url',
		'profile_image_url_https',
		'profile_image_url',
		'verified',
		'suspended',
		'needs_phone_verification',
		'creation_date',
	],
	$dates = [
		'creation_date',
	],
	$casts = [
		'verified' => 'bool',
		'suspended' => 'bool',
		'needs_phone_verification' => 'bool',
	];
	
	public function user() {
		return $this->belongsTo(\App\User::class);
	}
}
