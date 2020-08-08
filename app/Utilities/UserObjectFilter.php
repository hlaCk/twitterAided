<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13/10/2019
 * Time: 08:30 م
 */

namespace App\Utilities;

use App\User;

class UserObjectFilter {
	
	public $entity;
	public $filters = [];
	
	public function __construct($entity = null) {
		if(isset($entity) && $entity) {
			$this->entity = $entity;
		}
	}
	
	public function is($filters = []) {
		$filters = count($filters) ? $filters : $this->filters;
		foreach ($filters as $f => $v) {
			if(method_exists($this, $f) && $this->{$f}() !== $v)
				return false;
		}
		
		return true;
	}
	
	public function filter($filter, $value = true) {
		if(is_array($filter)) {
			foreach ($filter as $f=>$v) {
				$this->filter($f, $v);
			}
		} else {
			$this->filters[$filter] = $value;
		}
		
		return $this;
	}
	
	public function isLiveFollowing($entity = null) {
		return !!($entity = $entity ?: $this->entity)->live_following;
	}
	
	public function isVerified($entity = null) {
		return !!($entity = $entity ?: $this->entity)->verified;
	}
	
	public function isProtected($entity = null) {
		return !!($entity = $entity ?: $this->entity)->protected;
	}
	
	public function isFollowing($entity = null) {
		return !!($entity = $entity ?: $this->entity)->following;
	}
	
	public function isFollowSent($entity = null) {
		return !!($entity = $entity ?: $this->entity)->follow_request_sent;
	}
}