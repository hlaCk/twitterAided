<?php
namespace App\Http\Controllers;

// ini_set('max_execution_time', 0); //0=NOLIMIT
// set_time_limit(43200);
use App\Models\Crons\ToDoList;
use App\Models\User\Profile;
use App\Models\HT;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Spatie\Fractalistic\ArraySerializer;

class UtlController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function limiter_index(Request $request) {
		$user = $request->has('user_id') ? User::status()->find($request->get('user_id')) : AuthUser();
		
		$uTwitter = $user->getTwitter();
		$uTwitter->getRateLimit('account,followers,users');
		
		$followersLimit 	= $uTwitter->getFollowersLimit();
		$credentialsLimit 	= $uTwitter->getCredentialsLimit();
		$usersLimit 		= $uTwitter->getUsersLimit();
		$followersIdsLimit 	= $uTwitter->getFollowersLimit('ids');
		
		$data = [
			self::transformRateLimit($followersLimit, !0),
			self::transformRateLimit($credentialsLimit, !0),
			self::transformRateLimit($usersLimit, !0),
			self::transformRateLimit($followersIdsLimit, !0)
		];
		
		$hasUser = (isset($user) && $user && $user->exists) || ($user = User());
		if($hasUser) {
			$user = HomeController::transformList($user);
		}
		
		$title = 'Limits';
		$extra_title = ($user && $user['t_name'] ? "{$user['t_name']}" : false);
		
		return view('limiter_index', compact('data', 'user', 'hasUser', 'title', 'extra_title'));
	}
	
	public function data_index() {
		return response()->json([
			'req'=>\request()->all(),
			'users'=>User::all()
		]);
	}
	/**
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 *
	 * @throws \Throwable
	 */
	public function refresh_limiter_ajax(Request $request) {
		if (!($_res = $request->get('res', false))) {
			return \response()->json([
				'error' => true,
				'data'  => [
					'res is missing'
				]
			]);
		}
		
		if ($_res === '*') {
			return response()->json([
				'error' => false,
				'data'  => [
					view('layouts.partials.limits_table', [
							'load_scripts' => false,
							'data'    => $this->transformAll()]
					)->render()
				],
			]);
		}
		
		$_res = explode('@', $_res);
		if(!$_res[0]) return response()->json([
						'error' => true,
						'data'  => [
							'resources is missing'
						],
					]);
			
		$limits = AuthUser()->getTwitter()->{$_res[0]}($_res[1] ?: null);
		
		return response()->json([
			'error' => false,
			'data'  => [
				self::transformRateLimit($limits, !0)
			],
		]);
	}


// region reading
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 * @deprecated
	 */
	public function read_friends_ajax(Request $request, User $user) {
		set_time_limit(43200);
		
		if ($request->has('user_id')) {
			$user = User::status()->find($request->get('user_id'));
		} else {
			$user = new User;
		}
		
		if (!($user = $user && $user->exists ? $user : false))
			return response()->json([
				'error' => true,
				'data'  => [
					__LINE__ . ':invalid request !'
				]
			]);
		
		$read = twitter()->getFriends($user);
		
		return response()->json([
			'error' => false,
			'data'  => [
				collect($read)->count()
			]
		]);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function read_friends(Request $request, User $user) {
		set_time_limit(43200);
		
		if (!$user->exists) d('no iser selected');
		
		if (!($user = $user && $user->exists ? $user : false)) d("invalid request !");
		
		$read = twitter()->getFriends($user);
		
		return response()->json([
			'error' => false,
			'data'  => [
				collect($read)->count()
			]
		]);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 * @deprecated
	 */
	public function read_followers_ajax(Request $request, User $user) {
		set_time_limit(43200);
		
		if($request->has('user_id')) {
			$user = User::status()->find($request->get('user_id'));
		} else {
			$user = new User;
		}
		
		if(!($user = $user && $user->exists ? $user : false))
			return response()->json([
								'error' => true,
								'data'  => [
									__LINE__ . ':invalid request !'
								]
							]);
		
		d(
		    $user->followers
        );
//		$user->followers()->delete();
		 $read = twitter()->getFollowers($user, $callback);

		 return response()->json([
		 			'error' => false,
		 			'data'  => [
		 				collect($read)->count()
		 			]
		 		]);
	}
	
	/**
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
	 */
	public function read_followers(Request $request, User $user) {
		set_time_limit(43200);
		
		if (!$user->exists) d('no iser selected');
		
		if (!($user = $user && $user->exists ? $user : false))
			d("invalid request !");
		
		$read = 0;
		// store pulling cmd in db - cronjob
		$read = twitter()->getFollowers($user);
		d(
			$read
		);
		// $user->twitter()
		/*
		if (is_callable($rowCallback)) {
			// save friends/followers
			foreach ($users as $u) {
				$rowCallback($u, $user);
			}
		}*/
		
		return response()->json([
			'error' => false,
			'data'  => [ intval($read) ]
		]);
	}
	
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \App\User                $user
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function todo_read_followers_ajax(Request $request, User $user) {
		/**
		 * @param array|Arrayable|mixed	$data
		 * @param bool					$error
		 *
		 * @return \Illuminate\Http\JsonResponse
		 */
		$response = function ($data, $error = false) {
			return response()->json([
				'error' => $error,
				'data'  => is_array($data) ? $data : collect($data)->toArray()
			]);
		};
		
		if (!$user->exists)
            return ApiResponse(toCollect([
                'count'=>intval($user->twitter('User not exist!')),
            ]))->setError(true);
//			return $response('User not exist!', true);

		if($user->toDoLists()->isReadFollowers()->count()) {
            return ApiResponse(toCollect([
                    'count'=>intval($user->twitter('followers_count')),
            ]))->setError(true)->setMessage('Action already in queue!');
		} else {
            $user->toDo()->toReadFollowers->note(currentRoute()->getName() . ":" . __LINE__);
			return ApiResponse(toCollect([
                'count'=>intval($user->twitter('followers_count')),
            ]));
		}
	}
	
// endregion reading
	
	public function transformAll() {
		$data = [];
		User::status()->orderBy('name')->get()
			->each(function ($user) use (&$data) {
				$data[] = self::transformList($user);
			});
		return $data;
    }
	
	public static function transformRateLimit($data, $toArray = true) {
		if (!$data) return null;
		
		$data = collect($data)->toArray();
		$data = is_array($data) && key($data) === 'resources' ? $data['resources'] : $data;
		
		if(!$data['family'] && is_array($data)) {
			$family = key($data);
			$data = $data[$family];
			$key = key($data);
			$data = $data[$key];
		}
		
		$limit = [
			'limit'         => $data['limit'],
			'remaining'     => $data['remaining'],
			'reset_s' 		=> ($reset=Carbon::createFromTimestamp($data['reset']))->diffInSeconds(),
			'reset'         => $reset->diffForHumans(),
			'family'		=> $family = ($data['family'] ?? null),
			'key'			=> $key = ($data['key'] ?? null),
			'method'		=> $data['method'] ?? "{$family}@{$key}",
			'api_family'	=> $data['api_family'] ?? null,
			'familyKey'		=> ($family ?: "") . ($family && $key ? " - {$key}" : ''),
		];
		
		return $toArray ? $limit : (object) $limit;
	}
	
	public static function transformList($user) {
		$profile = $user->profile ?: new Profile([
			'creation_date' => carbon()->now()
		]);
		
		return [
    		'limit' 		=> $user->limit,
    		'remaining' 	=> $user->remaining,
    		'reset' 		=> $user->reset,
			't_last_update'	=> $user->last_update->format('d-m-Y H:i a'),
			't_created_at'	=> $profile->creation_date->format('d-m-Y'),
		];
    }
	
	/**
	 * todo: for twitter
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function privacy_policy() {
		return view('privacy_policy', compact('data'));
	}
	
}
