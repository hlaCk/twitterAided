<?php
namespace App\Traits;

use App\Models\Crons\ToDoList;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;

trait ToDoListScopes
{
// region scopes
	
	/**
	 * $this->action('readFollowers')
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeIsReadFollowers($q) {
		return $q->action(ToDoList::ACTIONS['READ_FOLLOWERS']);
	}
	
	/**
	 * $this->action()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeAction($q, $act = null) {
		return $q->where('action', is_null($act) ? '!=' : '=', $act);
	}
	
	/**
	 * $this->finished()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeFinished($q) {
		return $q->where('finish_time', '!=', null);
	}
	
	/**
	 * $this->completed()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeCompleted($q) {
		return $q->where('status', '=', ToDoList::COMPLETED);
	}
	
	/**
	 * $this->notCompleted()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeNotCompleted($q) {
		return $q->where('status', '!=', ToDoList::COMPLETED);
	}
	
	/**
	 * $this->pending()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopePending($q) {
		return $q->where('status', '=', ToDoList::PENDING);
	}
	
	/**
	 * $this->note()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeNote($q, $note = null) {
		return $q->where('note', 'like', "%{$note}%");
	}
	
	/**
	 * $this->Id()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeId($q, $id = null) {
		return $q->where('id', $id);
	}
	
	/**
	 * $this->all()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeAll($q) {
		return $q->withoutGlobalScope('waiting');
	}
	
	/**
	 * $this->list()
	 *
	 * @param $q
	 *
	 * @return mixed
	 */
	public function scopeList($q) {
		return $q->all()->get();
	}
// endregion scopes
}