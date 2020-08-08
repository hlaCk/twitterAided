<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13/10/2019
 * Time: 08:30 م
 */

namespace App\Utilities;

use App\User;

class TwitterUserObject {
	const FILTERS_TPL = [
		// they follow me i didn't follow back.
		'follow_back'=>[
			'isFollowing'  => false,
			'isFollowSent' => false,
		],
		
		// they unfollow me i didn't unfollow back.
		'unfollow_back'=>[
			'isFollowing'  => false,
			'isFollowSent' => false,
		],
	];
	
	public $entity;
	
	private $oldCursor;
	
	/**
	 * Default options.
	 *
	 * @var array
	 */
	public $options = [
			'user_id'               => null,
			'skip_status'           => '1',
			'include_user_entities' => 'false',
			'cursor'				=> null,
			'count'					=> '200'
		];
	public $filter = [];
	
	public function __construct(User $entity = null) {
		if(isset($entity) && $entity) {
			$this->entity = $entity;
			$this->options['user_id'] = $this->entity->twitter('id');
		}
	}
	
	public function getFollowers($_options = []) {
		$_options = collect($this->options)
					->merge(collect($_options))
					->filter(function ($v) { return !is_null($v); })->toArray();
		
		$entity = $this->entity;
		
		// reach the limit
		// if(!$entity->getTwitter()->checkLimit('followers')) {
		// 	return [];
		// }
		
		$followers = $entity->getTwitter()->getFollowers($_options);
		if($followers && isset($followers->users)) {
			$filters = $this->filter;
			
			if(empty($filters)) {
				$users = $followers->users;
			} else {
				$users = collect($followers->users)->filter(function ($e) use ($filters) {
					return (new UserObjectFilter($e))->is($filters);
				});
			}
			
			$followers->users = collect($users)->toArray();
		}
		
		if(isset($followers->next_cursor) && $followers->next_cursor)
			$this->cursor($followers->next_cursor);
		
		return $followers;
	}
	
	public function cursor($cursor = null) {
		if(!is_null($cursor)) {
			$this->oldCursor = $this->option('cursor');
			$this->option('cursor', $cursor);
		} else
			return $this->option('cursor');
		
		return $this;
	}
	
	public function entity(User $entity = null) {
		if(!is_null($entity))
			$this->entity = $entity;
		else
			return $this->entity;
		
		return $this;
	}
	
	public function option($key = null, $value = null) {
		// getters
		if(is_null($key) && is_null($value))
			return $this->options;
		else if (!is_array($key) && !is_null($key) && is_null($value))
			return isset($this->options[$key]) ? $this->options[$key] : null;
		
		// setters
		else if(!is_null($key) && !is_null($value))
			$this->options[ $key ] = $value;
		else if(is_array($key) && !is_null($key) && is_null($value))
			foreach ($key as $oK=>$oV) {
				$this->options[$oK] = $oV;
			}
		
		
		return $this;
	}
	
	public function filter($filter, $valid = true) {
		if (is_array($filter)) {
			foreach ($filter as $f => $v) {
				$this->filter($f, $v);
			}
		} else {
			$this->filter[$filter] = $valid;
		}
		
		return $this;
		// $user_object =
		// 	collect($this->entity)
		// 		->filter(function ($e) {
		// 			return $e->following !== true && $e->follow_request_sent === false;
		// 		})->map(function ($e) use (&$user) {
		// 			$user->newCronList($e->screen_name, CronList::ACTIONS['FOLLOW_BACK']);
				// });
	}
	
}