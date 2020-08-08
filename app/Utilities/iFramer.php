<?php /** @noinspection PhpUndefinedMethodInspection */

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 25/8/2019
 * Time: 06:52 م
 */

namespace App\Utilities;


use App\Traits\Tools\HasiFrameLayouts;
use App\Zex\HEngine;
use App\Zex\Interfaces\iFrameLayoutsInterface;
use App\Zex\Interfaces\iFramerTokenControl;
use App\Zex\Plugins\HURL;
use PhpParser\Node\Expr\Instanceof_;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class iFramer
 *
 * @package App\Utilities
 */
class iFramer implements iFramerTokenControl {
	use HasiFrameLayouts;

	protected static $isInitiated = false;
	
	/**
	 * Cach storage
	 *
	 * @see isTokenRequired
	 *
	 * @var array
	 */
	public static $cached = [];
	
	/**
	 * Class constructor, run all checker.
	 *
	 * @uses redirectWithNewToken()
	 * @uses isRefreshTokenRequested()
	 *
	 * @return bool
	 */
	public static function init() {
		// check if refresh token requested
		if (self::isRefreshTokenRequested())
			self::redirectWithNewToken();
		
		return true;
	}
	
	/**
	 * Redirect and append new token to new page
	 *
	 * @param string|null $uri New uri to redirect to.
	 */
	public static function redirectWithNewToken($uri = null) {
		// break the recall, coz its looping throw self::init()
		self::$isInitiated = true;
		$query = self::appendTokenToUri($uri)
					->remove(self::getRefreshTokenRequestName())
					->toJson();
		
		self::handleRedirection($query);
	}
	
	/**
	 * Returns {@see \App\Zex\Plugins\HURL HURL Object} with token in query search
	 *
	 * @param string|null $uri Url to append token into it, when its null current uri will be used.
	 *
	 * @uses getToken()
	 * @uses url()
	 * @uses init()
	 * @uses HEngine::url
	 * @uses HEngine::set
	 * @uses getTokenRequestName()
	 *
	 * @return HURL {@see \App\Zex\Plugins\HURL HURL Object}
	 */
	public static function appendTokenToUri($uri = null): HURL {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		$uri = $uri ?: url()->current();
		$_uri = null;
		try {
			$_uri = HEngine::url($uri)
					->set([ self::getTokenRequestName() => is_null(($token = self::getToken())) ? "TOKEN" : $token]);
		} catch (\Exception $exception) {
			df(
				$exception
			);
		}
		
		return $_uri ? $_uri : df('$_uri = null!');
	}
	
	/**
	 * @inheritDoc
	 */
	public static function verifyToken(string $token): bool {
		if ($token)
			return $token == self::getToken();
		
		return false;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function handleRedirection($routeName) {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		$failRouteName = (is_string($routeName) ? url($routeName) : false) ?: route(self::getFailRouteName());
		$consoleLogText = __METHOD__ . " to: " . $routeName;
		
		echo <<<redirect
<script>
	let consoleText = '{$consoleLogText}';
	consoleText && console.log( consoleText );
	
	let toUri = '{$failRouteName}';
	// redirect using a named route || url
	if(toUri) {
		window.location.href = toUri;
	}
	// reload page
	else {
		window.location.href = window.location.href + (window.location.href.indexOf('?') && '&' || '?') + '__redirector=iframer';
	}
</script>
redirect;
		
		exit;
	}
	
	
#region is
	
	/**
	 * Check if **Main** layout is requested
	 *
	 * @see  $mainLOutRequestName
	 * @uses request()
	 *
	 * @return bool true|false
	 */
	public static function isMainRequested(): bool {
		return request()->has(self::getMainLayoutRequestName());
	}
	
	/**
	 * Check if refresh token is requested
	 *
	 * @uses getRefreshTokenRequestName()
	 * @uses request()
	 *
	 * @return bool true|false
	 */
	public static function isRefreshTokenRequested(): bool {
		return request()->has(self::getRefreshTokenRequestName());
	}
	
	/**
	 * Check if token requested
	 *
	 * @uses getTokenRequestName()
	 * @uses request()
	 *
	 * @return bool true|false
	 */
	public static function isTokenRequested(): bool {
		return request()->has(self::getTokenRequestName());
	}
	
	/**
	 * @inheritDoc
	 */
	public static function isTokenRequired() : bool {
		$required = (bool) self::getComponent()->isTokenRequired();
		
		if($required)
			if (!self::$isInitiated) self::$isInitiated = self::init();
		
		return $required;
	}

#endregion is

#region Gitters
	/**
	 * @inheritDoc
	 */
	public static function getRequestedToken() {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		return request()->get(self::getTokenRequestName(), null);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getMainLayoutRequestName(): string {
		return self::$mainLOutRequestName;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getTokenRequestName(): string {
		return self::$tokenRequestName;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getRefreshTokenRequestName(): string {
		return self::$refreshTokenRequestName;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getFailRouteName(): string {
		return self::$failRouteName;
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getToken(): string {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		$token = session()->get(self::getTokenRequestName(), VALUE_NONE);
		
		return ($token == VALUE_NONE) ? self::getNewToken() : $token;
	}
	
	/**
	 * Returns HTTP_REFERER url if exist, false otherwise
	 *
	 * @param \Illuminate\Http\Request|null $request request to chek referer within.
	 *
	 * @uses request()
	 * @uses $_SERVER
	 * @uses HEngine::url
	 *
	 * @return HURL|bool
	 * {@see \App\Zex\Plugins\HURL HURL object} or {@see boolean false} when HTTP_REFERER not found
	 */
	public static function getReferer($request = null) {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		$request = is_null($request) ? request() : $request;
		$_referer = $request->server('HTTP_REFERER') ?: ($GLOBALS['_SERVER']['HTTP_REFERER'] ?? false);
		
		return $_referer !== false ? HEngine::url($_referer) : $_referer;
	}
	
	/**
	 * Returns HTTP_REFERER url if exist, hurl($default) otherwise.
	 *
	 * @param \Illuminate\Contracts\Routing\UrlGenerator|string|null $default Default return when no referer found.
	 *
	 * @uses request()
	 * @uses $_SERVER
	 * @uses HEngine::url
	 * @uses HURL::_ROOT
	 *
	 * @return HURL {@see \App\Zex\Plugins\HURL HURL object}
	 */
	public static function getRefererOr($default = HURL::_ROOT) {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		$request = request();
		$_referer = $request->server('HTTP_REFERER') ?: ($GLOBALS['_SERVER']['HTTP_REFERER'] ?? false);
		
		return $_referer !== false ? HEngine::url($_referer) : HEngine::url($default);
	}
	
	/**
	 * Generate new **Token** and return it.
	 *
	 * @uses init()
	 * @uses session()
	 * @uses str_random()
	 * @uses getTokenRequestName()
	 *
	 * @return string The new generated token
	 */
	public static function getNewToken(): string {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		session()->put(self::getTokenRequestName(), $session_token = str_random(5));
		session()->save();
		
		return $session_token;
	}
	
	/**
	 * Returns layout name depending on request or referer
	 *
	 * @uses iFrameLayoutsInterface::__callStatic()
	 * @uses iFrameLayoutsInterface::MAIN_LAYOUT        As {@link HasiFrameLayouts::MainLOut() MainLOut()}
	 * @uses iFrameLayoutsInterface::FRAMED_LAYOUT        As {@link HasiFrameLayouts::FramedLOut() FramedLOut()}
	 * @uses iFrameLayoutsInterface::MAIN_WEB_LAYOUT    As {@link HasiFrameLayouts::MainWebLOut() MainWebLOut()}
	 * @uses HasiFrameLayouts::$layouts    As {@link HasiFrameLayouts::LOut() LOut()}
	 *
	 * @see  debug()
	 * @see  initToken()
	 * @see  isMainRequested()
	 * @see  isTokenRequested()
	 * @see  requestedToken()
	 * @see  getReferer()
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\HttpException
	 * @return string Layout name
	 */
	public static function getLayout(): string {
		static $isDebuged;
		$layout = null;
		
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		
		if (!$isDebuged) {
			// debugout the token
			self::debug(
					getLastDebugBackTrace(),
					'session_token: ' . self::getToken(),
					'isMainRequested: ' . self::isMainRequested(),
					'isTokenRequested: ' . self::isTokenRequested(),
					'isTokenRequested: ' . self::isTokenRequested()
				);
		}
		
		$component 	= self::getComponent();
		$layout 	= self::getComponentLayOut();
		
		// token not required
		if(!$component->isTokenRequired()) {
			$layout = $layout ?: self::MainLOut();
			
			if (!$isDebuged)
				// debugout the layout
				self::debug(
					getLastDebugBackTrace(),
					"layout sent: {$layout}"
				);
			
			// stop debuging
			$isDebuged = true;
			return $layout;
		}
		
		/**
		 * main lauout - iframe container
		 * get layout by request
		 */
		if (self::isMainRequested()) {
			$layout = self::MainLOut();
		} /**
		 * iframe layout - iframe content
		 *
		 * get layout by token
		 */
		else if (self::isTokenRequested()) {
			// check token
			if (self::verifyToken(self::getRequestedToken())) {
				$layout = self::FramedLOut();
			} else {
				throw new HttpException(500, "Session token missmatch!", new \Exception("(IsTokenRequested: " . self::isTokenRequested() . " " .
					"IsTokenRequest: " . self::getToken() . " " .
					")", 1412));
			}
		} /**
		 * iframe layout - iframe content
		 *
		 * get layout by referer token
		 */
		else if (($_referer = self::getReferer()) && self::verifyToken($_referer->get(self::getTokenRequestName(), false))) {
			$layout = self::FramedLOut();
		} /**
		 * main web layout - no iframe, menu & content within one page
		 *
		 * no request, no token, no referer
		 *
		 * ** DEFAULT LAYOUT **
		 */
		else {
			$layout = self::MainLOut();
		}
		
		if (!$isDebuged)
			// debugout the layout
			self::debug(
				getLastDebugBackTrace(),
				"layout sent: {$layout}"
			);
		
		if (is_null($layout))
			df($layout);
		
		// stop debuging
		$isDebuged = true;
		return $layout;
	}
	
	/**
	 * Returns all layouts.
	 *
	 * @deprecated No need for this function
	 * @return array {@see $layouts}
	 */
	public static function getLayouts() {
		if (!self::$isInitiated) self::$isInitiated = self::init();
		
		return (array) self::LOut();
	}
	
	/**
	 * @inheritDoc
	 */
	public static function getComponentLayOut(): string {
		static $isDebuged;
		$component = self::getComponent();
		
		$layout = '';
		if ($component instanceof iFrameLayoutsInterface) {
			if(!($layout = $component->getLayOut())) {
				if (!$isDebuged)
					du(
					getLastDebugBackTrace(),
						'component: ' . get_class($component) .
						' returns null layout, returning default layout [Main layout]'
					);
				
				$layout = self::MainLOut();
			}
		} else {
			df(
			'iFrameLayoutsInterface not implemented in component: ' . get_class($component)
			);
		}
		
		$isDebuged = true;
		return $layout ?: self::MainLOut();
	}
	
	/**
	 * @property iFrameLayoutsInterface component
	 *
	 * @inheritDoc
	 */
	public static function getComponent(): iFrameLayoutsInterface {
		$cRoute = CurrentRoute();
		
		$component = null;
		if (array_key_exists($cRoute->getName(), self::$cached)) {
			$component = self::$cached[ $cRoute->getName() ];
		} else {
			$component = $cRoute->getController();
			
			self::$cached[$cRoute->getName()] = $component = [
				// control component
				'component'          => $cRoute->getController(),
				
				// 'hasGetLayOut'       => (bool) method_exists($component, 'getLayOut'),
				'getLayOut'			 => (bool) $component->getLayOut(),
				
				// 'hasIsTokenRequired' => (bool) method_exists($component, 'isTokenRequired'),
				'isTokenRequired' 	 => (bool) $component->isTokenRequired(),
				
				'isIFramerComponent' => (bool) ($component instanceof iFrameLayoutsInterface),
			];
			
			if (!$component['isIFramerComponent'])
				df(
				'iFrameLayoutsInterface not implemented'
				);
		}
		
		return $component[ 'component' ];
	}
	
	/**
	 * Returns global vars for iFramer elements as json.
	 *
	 * @return string
	 */
	public static function defaults() {
		$data 	= collect([]);
		$ref 	= iFramer::getRefererOr();
		$crnt 	= HURL::fromString();
		
		// myID
		$data->put('myID', $crnt->get('__id', $ref->get('__id', '')));
		
		// currentURL
		$data->put('currentURL', $crnt->getFullUrl());
		
		// titleSuffix
		$data->put('titleSuffix', ' - ' . setting('general.company_name'));
		
		return $data->toJson(JSON_PRETTY_PRINT);
	}
	
#endregion Gitters

}

/*
 * a.contentDocument.title
 * Whoops! There was an error.
 *
 * */