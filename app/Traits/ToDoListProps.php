<?php
namespace App\Traits;

use App\Models\Crons\ToDoList;

trait ToDoListProps
{
// region update ToDoList
	/**
	 * Set status as completed
	 *
	 * @return $this
	 */
	public function complete() {
		$this->status = ToDoList::COMPLETED;
		
		$this->save();
		
		return $this;
	}
	
	/**
	 * Set finish_time = now(), status = completed
	 *
	 * @return $this
	 */
	public function finish() {
		$this->finish_time = carbon()->now();
		$this->complete();
		
		return $this;
	}
	
	/**
	 * Set run_time = now()
	 *
	 * @return $this
	 */
	public function run() {
		$this->run_time = carbon()->now();
		$this->save();
		
		return $this;
	}
	
	/**
	 * Set note
	 *
	 * @return $this
	 */
	public function note($note) {
		$this->note = $note;
		$this->save();
		
		return $this;
	}
	
	/**
	 * Set action
	 *
	 * @return $this
	 */
	public function action($action = ToDoList::DEFAULT_ACTION) {
		$this->action = $action;
		$this->save();
		
		return $this;
	}
// endregion update ToDoList

}