<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 27/10/2019
 * Time: 07:34 م
 */

namespace App\Http\Middleware;


use Illuminate\Support\Facades\Session;

class VerifyAdminLogin {
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 *
	 * @return mixed
	 *
	 * @throws \Illuminate\Session\TokenMismatchException
	 */
	public function handle($request, \Closure $next) {
		if (isLogged() && intval(Session::get('as_admin', '0')) === 1) {
			
			if (intval(Session::get('as_admin_confirm', '0')) === 0) {
				if(!starts_with(currentRoute()->getName(), 'twitter.confirm_admin'))
					return redirect(route('twitter.confirm_admin'))
						->with('flash_notice', 'please confirm!');
				
				return $next($request);
			}
			
			if (starts_with(currentRoute()->getName(), 'twitter.confirm_admin')) {
				return redirect()->route('home');
			}
		} else if(isLogged() && intval(Session::get('as_admin', '0')) === 0){
			if (starts_with(currentRoute()->getName(), 'twitter.confirm_admin')) {
				return redirect()->route('home');
			}
		}
		
		return $next($request);
	}
}