<?php
namespace App\Traits;

use App\Models\Crons\ToDoList;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;

trait WithToDoList
{
	/**
	 * Returns ToDoList
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function toDoLists($id = null) {
		if(is_null($id))
			/** @var $this HasRelationships */
			return $this->morphMany(ToDoList::class, 'entity');
		else
			return $this->toDoLists()->Id($id)->first();
	}
	
	public function allToDoLists() {
		return $this->toDoLists()->all();//->get();
	}
	
	/**
	 * Create ToDo with this entity
	 *
	 * @param null|string|array|\Illuminate\Contracts\Support\Arrayable $data
	 *
	 * @return \App\Models\Crons\ToDoList|array|null
	 */
	public function toDo($data = []) {
		$data = is_string($data) ? ['action' => $data] : $data;
		$todo = $this->newToDo($data);
		
		$todo->action = $todo->action ?: ToDoList::DEFAULT_ACTION;
		$this->toDoLists()->save($todo);
		
		return $todo;//$this;
	}
	
	/**
	 * Returns new ToDoList Object
	 *
	 * @param null $data
	 *
	 * @return \App\Models\Crons\ToDoList
	 */
	public function newToDo($data = null) {
		$data = $data ?: ToDoList::DEFAULT_ACTION;
		$data = is_string($data) ? ['action' => $data] : $data;
		$todo = $data instanceof ToDoList ? $data : new ToDoList(collect($data)->toArray());
		
		return $todo;
	}
	
}