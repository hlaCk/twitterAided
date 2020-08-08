<?php

namespace App\Http\Controllers;

use App\Models\Crons\ToDoList;
use App\Models\User\Profile;
use App\Models\HT;
use App\TUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except([
        	'privacy_policy',
			'terms_of_service'
		]);
    }
	
	/**
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
	 */
    public function refresh_account_ajax(Request $request)
    {
    	if(!($_id = $request->get('id', false))) {
    		return \response()->json([
    			'error'=> true,
				'data'=>[
					'id is missing'
				]
			]);
		}
		
		if($_id === '*') {
    		return response()->json([
				'error' => false,
				'data'  => [
					view('layouts.partials.users_table', [
						'load_scripts' => false,
						'data' => $this->transformAll()]
					)->render()
				],
			]);
		}
		
		$user = User::status()->find($_id);
		// d($user->getTwitter()->getCredentials());
		$user->updateUser();
		
    	return response()->json([
    		'error'=>false,
    		'data'=>[
				self::transformList($user)
			],
		]);
    }
    
	/**
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
	 */
    public function disable_account_ajax(Request $request)
    {
    	if(!($_id = $request->get('id', false))) {
    		return \response()->json([
    			'error'=> true,
				'data'=>[
					'id is missing'
				]
			]);
		}
		
		if($_id === '*') {
    		return response()->json([
				'error' => false,
				'data'  => [
					view('layouts.partials.users_table', [
						'load_scripts' => false,
						'data' => $this->transformAll()]
					)->render()
				],
			]);
		}
		
		$user = User::status()->find($_id);
  
    	$oldStatus = $user && $user->status ? $user->status : null;
		// d($user->getTwitter()->getCredentials());
		$user && $user->update([
			'status'=> $user->status !== User::STATUS['INACTIVE'] ? User::STATUS['INACTIVE'] : User::STATUS['ACTIVE']
		]);
		
    	return response()->json([
    		'error'=>false,
    		'data'=>
				!$user || $user->status === User::STATUS['INACTIVE'] || $oldStatus!==$user->status? [] : [self::transformList($user)]
			,
		]);
    }
    
	/**
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
	 */
    public function delete_account_ajax(Request $request)
    {
    	if(!($_id = $request->get('id', false))) {
    		return \response()->json([
    			'error'=> true,
				'data'=>[
					'id is missing'
				]
			]);
		}
		
		if($_id === '*') {
    		return response()->json([
				'error' => false,
				'data'  => [
					view('layouts.partials.users_table', [
						'load_scripts' => false,
						'data' => $this->transformAll()]
					)->render()
				],
			]);
		}
		
		$user = User($_id);
		// d($user->getTwitter()->getCredentials());
		$deleted = false;
		$user && ($deleted = $user->delete());

    	return response()->json([
    		'error'=>!$deleted,
    		'data'=>$deleted ? [] : ($user ? $user->toArray() : []),
		]);
    }
    
	public function transformAll($query = null) {
    	$q = $query ?: User::query();
		$data = [];
		$q->orderBy('name')->get()->each(function ($user) use (&$data) {
			$data[] = self::transformList($user);
		});
		return $data;
    }
    
	public static function transformList($user) {
    	$profile = $user->profile ?: new Profile([
    		'creation_date'=>carbon()->now()
		]);
			
    	return [
			'id'=>$user->id,
			't_id'=>$user->twitter('id'),
			't_image'=>"<img src='{$profile->profile_image_url_https}'>",
			't_name'=>$user->twitter('name'),
			't_screen_name'=> HT::linkify($user->twitterName()),
			't_followers_count'=>$user->twitter('followers_count'),
			't_following_count'=>$user->twitter('friends_count'),
			't_is_suspended'=> $profile->suspended ? "Y" : "N",
			't_last_update'=>$user->last_update->format('d-m-Y h:i a'),
			't_created_at'=> $profile->creation_date->format('d-m-Y h:i a'),
		];
    }
    
    
    // region views
	
	/**
	 * todo: for twitter
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function privacy_policy() {
		return view('privacy_policy', compact('data'));
	}
	
	/**
	 * todo: for twitter
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function terms_of_service() {
		return view('terms_of_service', compact('data'));
	}
	
	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function index() {
		// $t = new ToDoList(['action' => 'test']);
		// User()->toDo('Mill');
		/*d(
			// User()->toDoLists(6)->note('NNN'),//toDo('MillN')->note('TestNote'),
			// User()->toDo('MillN2')->note('TestNote2'),
			User()->toDoLists//->each->finish()//->get()//()->save($t)
		);*/
		/*d(
			User()->userToDoLists()->list(),
			User()->userToDoLists,
			User()->toDoLists,
			User()->id
		);*/
		// d(
		// 	User()->toDoLists()->isReadFollowers()->get(),
		// 	User()->toDoLists->filter(function ($t) {
		// 		return ($t->action === ToDoList::ACTIONS['READ_FOLLOWERS']);
		// 	})
			// ((array) User()->toDoLists[0]->actions)['mm'](),
			// User()->toDoLists[0]->actions->mm()
		// 1
		// );
		$data = [];
		$data[] = self::transformList(AuthUser());
		$title = AuthUser()->twitterName();
		return view('home', compact('data', 'title'));
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function accounts_list() {
		$data = $this->transformAll();
		$title = 'All accounts';
		
		return view('accounts_list', compact('data', 'title'));
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function disabled_accounts_list() {
		$data = $this->transformAll(User::InActive());
		$title = __('All disabled accounts');
		
		return view('accounts_list', compact('data', 'title'));
	}
	
	// endregion views
}
