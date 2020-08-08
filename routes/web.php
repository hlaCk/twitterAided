<?php
use \Illuminate\Support\Facades\Input;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/homeEmpty', function () {
	return view('home-empty', ['title'=>'Empty Index']);
});

Route::get('/', function () {
	
	// return redirect()->to(route('home'));
    return view('welcome');
});

Route::get('/userTimeline/{screen_name?}/{count?}/{format?}', function ($screen_name=null, $count = null, $format = null) {
	if(!$screen_name) dd('no Text', $screen_name);
	if(!$count) $count = 2;
	if(!$format) $format = 'array';
	
	$data = Twitter::getUserTimeline(['screen_name' => $screen_name, 'count' => $count, 'format' => $format]);
	dd(
		collect($data)
	);
	return $data;
});
Route::get('profile', function () {

# region debug
	try {
		$response = HT::getUserTimeline(['count' => 2, 'format' => 'array']);
	} catch (Exception $e) {
		// dd(Twitter::error());
		dd(HT::logs());
	}
	
	dd($response);
# endregion debug

	
	// Only authenticated users may enter...
})->middleware('auth');

Route::get('credentials', function () {
	// terst
	$tuser = \App\TUser::status()->where('user_id', CurrentTUser('user_id'))->get();
	$tuser = $tuser->count() ? $tuser->first() : \App\TUser::registerTUser(CurrentTUser());
	d(CurrentTUser(), $tuser);
	
# region credentials
	$credentials = Twitter::getCredentials([
		'include_email' => 'true',
	]);
	
	dd($credentials);
# endregion credentials

});//->middleware('auth');


Route::get('data', function () {
	/**
	 * Returns detailed information about the relationship between two arbitrary users.
	 *
	 * Parameters :
	 * - source_id
	 * - source_screen_name
	 * - target_id
	 * - target_screen_name
	 */
	$u = Twitter::getFriendships([
		'source_screen_name'=>'3fofC',//AuthUser()->twitter('id'),
		'target_screen_name'=> 'alhlaCk',//AuthUser()->twitter('id'),
		// 'count'=>1
	]);
	d(
		$u
	);

})->middleware('auth');

// Auth::routes();

Route::get('/home', 'HomeController@index')->name('home')->middleware('auth');
Route::get('/accounts_list', 'HomeController@accounts_list')->name('accounts_list');
Route::get('/disabled_accounts_list', 'HomeController@disabled_accounts_list')->name('disabled_accounts_list');

// post not any
Route::any('/refresh_account', 'HomeController@refresh_account_ajax')->name('ajax.refresh_account');
Route::any('/disable_account', 'HomeController@disable_account_ajax')->name('ajax.disable_account');
Route::any('/delete_account', 'HomeController@delete_account_ajax')->name('ajax.delete_account');


// define("callbacktw", "http://decodercan.com/tw/callback.php");
Route::prefix('twitter')->as('twitter.')->group(function() {
	
	Route::get('confirm_admin', 'LoginController@confirm_admin')
		->name('confirm_admin');//->middleware('auth');
	
	Route::put('confirm_admin', 'LoginController@confirm_admin_update')
		->name('confirm_admin_update')->middleware('auth');
	
	Route::get('loginAsAdmin', 'LoginController@loginAsAdmin')
		->name('loginAsAdmin')->middleware('auth.basic');
	
	Route::get('login', 'LoginController@login')
		->name('login');
	
	Route::get('callback', 'LoginController@callback')
		->name('callback');
	
	Route::get('error', 'LoginController@error')
		->name('error');
	
	Route::post('logout', 'LoginController@logout')
		->name('logout');
	Route::get('logoutUser', 'LoginController@logout')
		->name('logoutUser');
	
	Route::get('privacy_policy', 'HomeController@privacy_policy')->name('privacy_policy');
	Route::get('terms_of_service', 'HomeController@terms_of_service')->name('terms_of_service');

});

Route::prefix('utl')->as('utl.')->group(function() {
	// Route::get('limiter/{user}', 'UtlController@limiter_index')->name('limiter_index');
	Route::post('data', 'UtlController@data_index')->name('data_index');
	Route::get('limiter', 'UtlController@limiter_index')->name('limiter_index');
	
	Route::post('refresh-limiter', 'UtlController@refresh_limiter_ajax')->name('ajax.refresh_limiter');
	
	// followers
	Route::get('read_followers_by_id/{user?}', 'UtlController@read_followers')->name('read_followers');
	Route::post('read_followers/{user?}', 'UtlController@read_followers_ajax')->name('ajax.read_followers');
	Route::post('todo/read_followers/{user}', 'UtlController@todo_read_followers_ajax')->name('ajax.read_followers.todo');
	
	// friends
	Route::get('read_friends_by_id/{user?}', 'UtlController@read_friends')->name('read_friends');
	Route::post('read_friends/{user?}', 'UtlController@read_friends_ajax')->name('ajax.read_friends');
	
});