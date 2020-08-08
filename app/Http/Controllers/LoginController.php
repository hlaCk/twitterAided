<?php

namespace App\Http\Controllers;

use App\Models\Auth\User;
use App\Models\User\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Thujohn\Twitter\Facades\Twitter;

class LoginController extends Controller
{
	
	/**
	 * Login via twitter - redirect to twitter thren to callback.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function loginAsAdmin(Request $request) {
		self::logout();
		
		// your SIGN IN WITH TWITTER  button should point to this route
		$sign_in_twitter 	= true;
		$force_login 		= true;
		
		// Make sure we make this request w/o tokens, overwrite the default values in case of login.
		Twitter::reconfig(['token' => '', 'secret' => '']);
		$token = Twitter::getRequestToken(defined('callbacktw') ? callbacktw : route('twitter.callback'));
		
		if (isset($token['oauth_token_secret'])) {
			$url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);
			
			Session::put('as_admin', '1');
			Session::put('as_admin_confirm', '0');
			Session::put('oauth_state', 'start');
			Session::put('oauth_request_token', $token['oauth_token']);
			Session::put('oauth_request_token_secret', $token['oauth_token_secret']);
			
			return Redirect::to($url);
		}
		
		return Redirect::route('home');
    }
	
	/**
	 * Login via twitter - redirect to twitter thren to callback.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function login(Request $request) {
		if (!$request->has('relogin') && Auth::check()) {
			return redirect()->to(route('home'));
		}
		
		// your SIGN IN WITH TWITTER  button should point to this route
		$sign_in_twitter 	= true;
		$force_login 		= $request->has('relogin');
		
		// Make sure we make this request w/o tokens, overwrite the default values in case of login.
		Twitter::reconfig(['token' => '', 'secret' => '']);
		$token = Twitter::getRequestToken(defined('callbacktw') ? callbacktw : route('twitter.callback'));
		
		if (isset($token['oauth_token_secret'])) {
			$url = Twitter::getAuthorizeURL($token, $sign_in_twitter, $force_login);
			
			Session::put('as_admin', '0');
			Session::put('oauth_state', 'start');
			Session::put('oauth_request_token', $token['oauth_token']);
			Session::put('oauth_request_token_secret', $token['oauth_token_secret']);
			
			return Redirect::to($url);
		}
		
		return Redirect::route('twitter.error');
    }
	
	/**
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function callback(Request $request) {
		// You should set this route on your Twitter Application settings as the callback
		// https://apps.twitter.com/app/YOUR-APP-ID/settings
		if (Session::has('oauth_request_token')) {
			$request_token = [
				'token'  => Session::get('oauth_request_token'),
				'secret' => Session::get('oauth_request_token_secret'),
			];
			
			Twitter::reconfig($request_token);
			
			$oauth_verifier = false;
			if (Input::has('oauth_verifier')) {
				$oauth_verifier = Input::get('oauth_verifier');

				// getAccessToken() will reset the token for you
				$token = Twitter::getAccessToken($oauth_verifier);
			}
			
			if (!isset($token['oauth_token_secret'])) {
				return Redirect::route('twitter.error')->with('flash_error', 'We could not log you in on Twitter.');
			}
			
			$credentials = Twitter::getCredentials([
				'include_email' => 'true',
			]);
			
			if (is_object($credentials) && !isset($credentials->error)) {
				// $credentials contains the Twitter user object with all the info about the user.
				// Add here your own user logic, store profiles, create new users on your tables...you name it!
				// Typically you'll want to store at least, user id, name and access tokens
				// if you want to be able to call the API on behalf of your users.
				
				// This is also the moment to log in your users if you're using Laravel's Auth class
				// Auth::login($user) should do the trick.
				
				Session::put('access_token', $token);
				$credentials = collect($credentials)->merge($token);
				$find_user = User::status()->where('t_id', $credentials->get('id'))->first();//->get();
				
				if (is_null($find_user) || ($find_user && !$find_user->count())) {
					$find_user = \App\User::registerUser($credentials);
				} else {
					// $find_user = $find_user->first();
				}
				
				if ($find_user && $find_user->count()) {
					$find_user->loginMe();
					// Auth::login($find_user);
					
					$userAdmin = Admin::getByUser($find_user->id);
					$isUserAdmin = $userAdmin && intval($userAdmin->admin) == 1;
					Session::put('as_admin_confirm', trim($isUserAdmin));
					
					return Redirect::to(route('home'))
							->with('flash_notice', 'Congrats! You\'ve successfully signed in!');
				} else {
					return Redirect::to('/')
							->with('flash_error', 'Crab! Something went wrong while find your user!');
				}
			}
			
			return Redirect::route('twitter.error')
					->with('flash_error', 'Crab! Something went wrong while signing you up!');
		} else {
			return \redirect()->route('twitter.login');
		}
    }
	
	public function confirm_admin(Request $request) {
		$find_user = Authuser();
		$isAdmin = $find_user->admin ? $find_user->admin->admin : false;
		
		if($isAdmin) {
			Session::put('as_admin_confirm', '1');
			return \redirect()->route('home');
		}
		
		$confirm = [
			'message'=> __('Allow this user to be admin ?'),
			'user'=> $find_user->twitter('name') . ' - ' . $find_user->twitterName()
		];
		$loadNavBar = false;
		
		return view('confirm_admin', compact(
			'confirm',
			'loadNavBar'
		));
    }
	
	public function confirm_admin_update(Request $request) {
		$find_user = \User();
		if($request->has('result')) {
			$requestResult = intval($request->get('result')) === 1 || $request->get('result') === "true";
			$userAdmin = AuthUser()->admin;
			
			if($requestResult) {
				if (!$userAdmin) {
					$find_user->admin()->create([
						'admin' => 1,
					]);
				}
			} else if ($requestResult) {
				if ($userAdmin) {
					$userAdmin->delete();
				}
				
				return response()->json([
					'logout' => true,
					'error' => false,
					'url'   => route('twitter.logoutUser')
				], 200);
			}
			
			return response()->json([
				'error'=> false,
				'url'=>route('home')
			], 200);
			
		}
		
		
		return response()->json([
			'error' => true,
			'url'   => route('twitter.confirm_admin_update')
		], 500);
	}
	
	public function error(Request $request) {
		// Something went wrong, add your own error handling here
	}
	
	public function logout(Request $request = null) {
		Session::forget('access_token');
		Auth::logout();
		
		return redirect()->to('/')->with('flash_notice', 'You\'ve successfully logged out!');
	}
}
