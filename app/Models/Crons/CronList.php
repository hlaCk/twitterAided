<?php

namespace App\Models\Crons;

use App\User;
use App\Utilities\TwitterUserObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CronList extends Model
{
	/**
	 * CronList default actions.
	 */
	const ACTIONS = [
		'FOLLOW_BACK'		=> 'follow_back',
		'CHECK_FOLLOW_BACK' => 'check_follow_back',
		
		'UNFOLLOW'			=> 'unfollow',
		'CHECK_UNFOLLOW' 	=> 'check_unfollow',
		
		'FOLLOW'			=> 'follow',
		'REFRESH'			=> 'refresh',
	];
	
	protected $fillable = [
			'user_id',
			'target_name',
			'action',
			'complete',
			'error',
		],
		$dates = [
			'complete'
		];
	
	
	public function user() {
		return $this->belongsTo(User::class);
	}
	
	/**
	 * Get CronList.
	 *
	 * @param array $data
	 *
	 * @return \Illuminate\Support\Collection|null
	 */
	public static function getCronList($data = []) {
		$data = collect($data);
		
		if($data->count() === 0) {
			$data = CronList::notCompleted()->get();
		} else {
			$data = CronList::notCompleted()->where($data->toArray())->get();
		}
		
		if (!$data || !$data->count()) return null;
		
		return collect($data);
	}
	
	/**
	 * *<u>cronjob function</u>*
	 *
	 * #Check who follow me & i didn't follow back.
	 *
	 *
	 *
	 * **Method `getFollowers` :**
	 * ```
 	 * - Returns a cursored collection of user objects for users following the specified user.
	 *
	 * Parameters :
	 * - user_id
	 * - screen_name
	 * - cursor
	 * - skip_status (0|1)
	 * - include_user_entities (0|1)
	 * ```
	 *
	 * @param User|integer|null $id User id, User model to read following from, or null to continue checking from DB.
	 *
	 * @return string|bool|null returns message or bool or null.
	 */
	public static function check_follow_back($id = null) {
		/**
		 * Requests Options.
		 */
		$_options = [
			'user_id'               => 0,
			'skip_status'           => '1',
			'include_user_entities' => 'false',
			'cursor'                => null,
			'count'                 => '200',
		];
		$_old_cron = null;
		$user = null;
		
		/**
		 * when $id = null
		 * run CHECK_FOLLOW_BACK from db.
		 */
		if (is_null($id)) {
			// todo
			/**
			 * Get last cursor value.
			 */
			if ($_old_cron = CronList::notCompleted()->action(CronList::ACTIONS['CHECK_FOLLOW_BACK'])->first()) {
				if ($_old_cron->action != CronList::ACTIONS['CHECK_FOLLOW_BACK']) {
					d("please remove ->first()");
				}
				
				$user = $_old_cron->user;
				$id = $_old_cron->user_id;
				
				if ($_old_cron->target_name)
					$_options['cursor'] = $_old_cron->target_name;
				
				if ($user)
					$_options['user_id'] = $user->twitter('id');
			}
			/**
			 * CronList is empty | start over
			 */
			else {
				// $rndUser = User::status()->all();
				$rndUser = User::all();
				$rndUser = $rndUser->get(rand(0, $rndUser->count() - 1));
				
				if($rndUser && $rndUser->exists) {
					$id = $rndUser->id;
					$user = $rndUser;
				} else {
					/**
					 * Can not load user
					 */
					return __LINE__ . ":Cann't load user!";
				}
			}
		}
		/**
		 * Check user.
		 */
		// else if (!($user = $id instanceof User ? $id : ($id ? User::status()->find($id) : null))) {
		else if (!($user = $id instanceof User ? $id : ($id ? User::find($id) : null))) {
			/**
			 * $id not exist
			 */
			return __LINE__ . ":User not found! ID: {$id}";
		}
		
		$fs = twitter()->getFollowers(AuthUser());
		
		d(
			$fs
		);
		
		if(!isset($_options['user_id']) || !$_options['user_id']) {
			if ($user)
				$_options['user_id'] = $user->twitter('id');
			else
				return __LINE__ . ":User not found! ID: {$id}";
		}
		
		$_options = collect($_options)->filter(function ($v) { return !is_null($v); })->toArray();
		
		// d(
		// 	$user
		// );
		$uo = (new TwitterUserObject($user))->option($_options);
		$uo = $uo->filter(TwitterUserObject::FILTERS_TPL['follow_back']);
		
		$myFollower = $uo->getFollowers();
		$myFollowerUsers = collect($myFollower ? ($myFollower->users??null) : null);
		
		/**
		 * Users found.
		 */
		if($myFollowerUsers->count()) {
			$myFollowerUsers->map(function ($e) use (&$user) {
				/**
				 * Push data to db - (for cronjob)
				 */
				$user->newCronList($e->screen_name, CronList::ACTIONS['FOLLOW_BACK']);
			});
		}
		/**
		 * No Users needs follow back.
		 */
		if($myFollower) {
			/**
			 * Push current cursor to db, to rerun after while.
			 */
			if ($myFollower->next_cursor) {
				$user->newCronList($myFollower->next_cursor, CronList::ACTIONS['CHECK_FOLLOW_BACK']);
			}
		}
		
		/**
		 * Mark current CronList as completed.
		 */
		if ($_old_cron && $_old_cron->target_name) {
			$_old_cron->complete = carbon()->now();
			$_old_cron->save();
		}
		
		d(
			$myFollower,
			$_old_cron,
			$_options
		);
		//
		// if ($limit <= 0 && $_options['cursor']) {
		// 	return "Croned<br>Limit: {$limit}";
		// } else {
		// 	return "Done<br>Limit: {$limit}";
		// }
		
	}
	
	
	public function scopeNotCompleted(Builder $query) {
		return $query->where('complete', '=', null);
	}
	
	public function scopeCompleted(Builder $query) {
		return $query->where('complete', '!=', null);
	}
	
	public function scopeAction(Builder $query, $action = null) {
		return $query->where('action',
			!is_null($action) ? '=' : '!=',
			!is_null($action) ? $action : null
		);
	}
	public function scopeCheckFollowBack(Builder $query, $action = null) {
		return $query->where('action',
			!is_null($action) ? '=' : '!=',
			!is_null($action) ? $action : null
		);
	}
	
	public function scopeUserId(Builder $query, $user_id = null) {
		return $query->where('user_id',
			!is_null($user_id) ? '=' : '!=',
			!is_null($user_id) ? $user_id : null
		);
	}
}
