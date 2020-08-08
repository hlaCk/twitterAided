<?php

namespace App\Models\Auth;

use App\Models\Crons\CronList;
use App\Models\Crons\ToDoList;
use App\Models\User\Admin;
use App\Models\User\Follower;
use App\Models\User\Friend;
use App\Traits\WithToDoList;
use App\TUser;
use App\Models\User\Profile;
use App\Utilities\Twitter;
use Illuminate\Support\Facades\Auth;
use Laratrust\Models\LaratrustPermission;
use Illuminate\Foundation\Auth\User as Authenticatables;
use Illuminate\Database\Eloquent\Builder;


class Authenticatable extends Authenticatables {
	use WithToDoList;
	
	// twitter instance for this user
	public static $twitterInstance = null;
	
	protected
		$table = 'users',
	
		$dates = [
			'last_update',
		],
		
		$appends = [
			'twitter',
			'user'
		];
	
	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];
	
	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];
	
	/**
	 * User columns (twitter) map
	 */
	const AT_COLUMNS_MAP = [
		'id'                 => 't_id',
		'name'               => 't_name',
		'screen_name'        => 't_screen_name',
		'oauth_token'        => 't_oauth_token',
		'oauth_token_secret' => 't_oauth_token_secret',
		
		'followers_count' 	=> 't_followers_count',
		'friends_count'   	=> 't_friends_count',
	];
	
	/**
	 * Status enum
	 */
	const STATUS = [
		'ACTIVE'=>'active',
		'INACTIVE'=>'inactive',
	];
	
	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot() {
		parent::boot();
		
		static::addGlobalScope('active', function (Builder $builder) {
			$builder->where('status', self::STATUS['ACTIVE']);
		});
		
		/**
		 * before delete() method call this
		 */
		static::deleting(function (User $user) {
			$user->profile()->delete();
			$user->followers()->delete();
			$user->friends()->delete();
			
			
		});
		
	}
	
	/**
	 * admin data.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function admin() {
		return $this->hasOne(Admin::class);
	}
	
	/**
	 * Twitter data.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function profile() {
		return $this->hasOne(Profile::class);
	}
	
	/**
	 * Twitter followers data.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function followers() {
		return $this->hasMany(Follower::class);
	}
	
	/**
	 * Twitter friends data.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function friends() {
		return $this->hasMany(Friend::class);
	}
	
	/**
	 * Returns toDoLists that created by me
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function userToDoLists() {
		return $this->hasMany(ToDoList::class, 'user_id');
	}
	
	public function scopeStatus($q, $status = null) {
		$q = $q->withoutGlobalScope('active');
		return $status ? $q->where('status', $status) : $q;
	}
	
	public function scopeInActive($q) {
		return $q->status(User::STATUS['INACTIVE']);
	}
	public function scopeActive($q) {
		return $q->status(User::STATUS['ACTIVE']);
	}
	
	public function isActive($user = null) {
		if (is_null($user)) {
			$user = $this;
		} else if (!($user instanceof Authenticatable))
			$user = User::status()->find($user);
		
		return $user && ($user->status === User::STATUS['ACTIVE']);
	}
	
	public function isAdmin($user = null) {
		if (is_null($user)) {
			$user = $this;
		} else if (!($user instanceof Authenticatable))
			$user = User::status()->find($user);
		
		return $user && ($admin = $user->admin) && intval($admin->admin) === 1 ? true : false;
	}
	
	/**
	 * Returns CronList of $this user
	 *
	 * @param string $action One of CronList::ACTIONS
	 *
	 * @return null|CronList
	 */
	public function getCronList($action = CronList::ACTIONS['CHECK_FOLLOW_BACK']) {
		$check = CronList::where([
			'user_id'     => $this->id,
			'action'      => $action,
			'complete'    => null,
		]);
		
		if (!$check->count()) return null;
		
		return $check->first();
	}
	
	/**
	 * Create nwe CronList belongsTo User.
	 *
	 * @param $target_name
	 * @param $action
	 *
	 * @return mixed
	 */
	public function newCronList($target_name, $action) {
		if(!$action) return false;
		
		$check = CronList::where([
			'user_id'     => $this->id,
			'target_name' => $target_name,
			'action'      => $action,
			'complete'    => null,
		]);
		
		if($check->count())
			/**
			 * @var $check
			 */
			return $check->first();
		
		return CronList::create([
			'user_id'		=> $this->id,
			'target_name'	=> $target_name,
			'action'		=> $action,
		]);
	}
	
	/**
	 * Register new user via twitter api.
	 *
	 * @param object $_userData is verified user credentials. using `->getCredentials()`.
	 *
	 * @return \App\Models\Auth\User|bool
	 */
	public static function registerUser($_userData) {
		
		$_userData = is_collection($_userData) ? $_userData : collect($_userData);
		$userData = self::credentialsTransform($_userData);
		
		if (!$userData->has('t_oauth_token') || !$userData->has('t_oauth_token_secret')) {
			return false;
		}
		
		$nUser = new User();
		$userData = $userData->only($nUser->getFillable());
		
		$nUser->fill($userData->toArray());
		$nUser->save();
		
		$profile = new Profile();
		$profile_data = $_userData->only($profile->getFillable())
							->forget('user_id')
							->put('creation_date', carbon()->parse($_userData->get('created_at')) );
		
		$nUser->profile()->create( $profile_data->toArray() );
		
		return $nUser;
	}
	
	/**
	 * update exist account. User/Profile ...
	 *
	 * @return $this
	 */
	public function updateUser() {
		$twitter 		= $this->getTwitter();
		$credentials 	= collect($twitter->getCredentials());
		
		$_userData = collect($credentials);
		$userData = self::credentialsTransform($_userData);
		$this->update(
			$userData->only($this->getFillable())->toArray()
		);
		
		$profile = new Profile();
		$profile_data = $_userData->only($profile->getFillable())
									->except(['user_id'])
									->put('creation_date', carbon()->parse($_userData->get('created_at')) );
								
		$this->profile()
			->updateOrCreate(
				$profile_data->toArray()
			);
		
		return $this;
	}
	
	/**
	 * Transform for Twitter profile data.
	 *
	 * @param $data ->getCredentials()
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public static function credentialsTransform($data) {
		$data = is_collection($data) ? $data : collect($data);
		
		$columns = self::AT_COLUMNS_MAP;
		$data = $data->mapWithKeys(function ($v, $i = null) use ($columns) {
			if ($i && isset($columns[$i]))
				return [
					$columns[$i] => $v
				];
			
			return [];
		});
		
		$data->put('last_update', carbon()->now());
		$data->put('name', $data->get('t_screen_name'));
		
		return $data;
	}
	
	/**
	 * Login user using $this
	 *
	 * @return bool|\Illuminate\Http\RedirectResponse
	 */
	public function loginMe() {
		if (!$this->id)
			return false;
		
		if($this->status === User::STATUS['INACTIVE']) {
			d(
				'User Banned'
			);
		}
		
		Auth::login($this);
		
		// Auth::loginUsingId(2);
		return redirect()->back();
	}
	
	/**
	 * Returns $this->>name, with @
	 *
	 * @param null|string $default Return $default if $this->name not found.
	 *
	 * @return null|string
	 */
	public function twitterName($default = null) {
		return $this->name ? "@{$this->name}" : $default;
	}
	
	/**
	 * Returns collection of twitter fields in $this User.
	 *
	 * @param $value
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getTwitterAttribute($value) {
		$columns = array_flip(self::AT_COLUMNS_MAP);
		
		$twitterAttr =
			fractal($this, function ($user) use ($columns) {
				$data = collect($user->attributes)
							->mapWithKeys(function ($v, $i = null) use ($columns) {
								if (starts_with($i, 't_'))
									return [
										$columns[$i] => $v
									];
								
								return [];
							});
		
				return $data->toArray();
			})->toArray();
		
		return collect($twitterAttr);
	}
	
	/**
	 * Returns collection of user fields in $this User.
	 *
	 * @param $value
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getUserAttribute($value) {
		$userAttr = fractal($this, function ($user) {
						$data = collect($user->attributes)
							->mapWithKeys(function ($v, $i = null) {
								if (!starts_with($i, 't_'))
									return [
										$i => $v
									];
								
								return [];
							});
			
						return $data->toArray();
					})->toArray();
		
		return collect($userAttr);
	}
	
	/**
	 * Alias for $this->twitter.
	 *
	 * @param null|string $value attr to return
	 *
	 * @return mixed|null
	 */
	public function twitter($value = null) {
		$_value = collect($value ? $this->twitter : null);
		return $value ? $_value->get($value, null) : $value;
	}
	
	/**
	 * Returns Twitter Api for $this User.
	 *  Credentials from db
	 *
	 * @return \App\Utilities\Twitter|null
	 */
	public function getTwitter() {
		if ($this->token_key)
			Twitter::$token_key = $this->token_key;
		
		if(is_null(self::$twitterInstance)) {
			self::$twitterInstance = twitter($this->getUserTokens());
		}
		
		return self::$twitterInstance;
	}
	
	/**
	 * Returns array of token & secret. from Credentials data or DB columns.
	 *
	 * @param array|null $data Credentials data or null
	 *
	 * @return array|null
	 */
	public function getUserTokens($data = null) {
		$data = $data ?: $this->twitter;
		$data = is_collection($data) ? $data : collect($data);
		
		return
			$data->count() &&
			$data->has('oauth_token') &&
				$data->has('oauth_token_secret') ? [
					'token'  => $data->get('oauth_token'),
					'secret' => $data->get('oauth_token_secret'),
				] : null;
	}
	
	
	/**
	 * save reciveed folloers and link it to User ($this)
	 *
	 * @param $followers
	 *
	 * @return int|bool number of accepted followers
	 */
	public function pushFollowers($followers) {
		$followers = collect($followers);
		if($followers->count() === 0) return false;
		
		$self = &$this;
		$followersCount = 0;
		$followers->map(function ($u) use(&$self, &$followersCount) {
			$u = collect($u)
					->only($self->getFillable())
					->toArray();
			
			$followersCount++;
			return $self->followers()->create($u);
		});
		
		// if(is_callable($rowCallback)) {
		// 	save friends/followers
			// foreach ($users as $u) {
			// 	$rowCallback($u, $user);
			// }
		// }
		
		return $followersCount;
	}
}