<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13/10/2019
 * Time: 08:30 م
 */

namespace App\Utilities;

use App\Http\Controllers\HomeController;
use App\Models\User\Follower;
use App\Models\User\Friend;
use App\User;
use RunTimeException;
use Carbon\Carbon as Carbon;
use Illuminate\Session\Store as SessionStore;
use Illuminate\Config\Repository as Config;
use tmhOAuth;

use Thujohn\Twitter\Traits\AccountTrait;
use Thujohn\Twitter\Traits\BlockTrait;
use Thujohn\Twitter\Traits\DirectMessageTrait;
use Thujohn\Twitter\Traits\FavoriteTrait;
use Thujohn\Twitter\Traits\FriendshipTrait;
use Thujohn\Twitter\Traits\GeoTrait;
use Thujohn\Twitter\Traits\HelpTrait;
use Thujohn\Twitter\Traits\ListTrait;
use Thujohn\Twitter\Traits\MediaTrait;
use Thujohn\Twitter\Traits\SearchTrait;
use Thujohn\Twitter\Traits\StatusTrait;
use Thujohn\Twitter\Traits\TrendTrait;
use Thujohn\Twitter\Traits\UserTrait;


class Twitter extends tmhOAuth {
	
	use AccountTrait,
		BlockTrait,
		DirectMessageTrait,
		FavoriteTrait,
		FriendshipTrait,
		GeoTrait,
		HelpTrait,
		ListTrait,
		MediaTrait,
		SearchTrait,
		StatusTrait,
		TrendTrait,
		UserTrait;
	
	/**
	 * Store the config values
	 */
	private $tconfig;
	
	/**
	 * Store the config values for the parent class
	 */
	private $parent_config;
	
	/**
	 * Only for debugging
	 */
	private $debug;
	
	private $log = [];
	
	private $error;
	
	/**
	 * @var string user twitter api token
	 */
	public static $token_key = null;
	
	/**
	 * rates limit store
	 *
	 * @var null
	 */
	public static $appRateLimitStore = null;
	
	/**
	 * Twitter constructor.
	 *
	 * @param array|\Illuminate\Support\Collection $data
	 */
	public function __construct($data = null) {
		if(!self::$token_key) {
			self::$token_key = TOKENS['DEFAULT'];
		}
		else if(self::$token_key != TOKENS['DEFAULT']) {
			// todo: multi api
			// collect(Config('ttwitter'))->merge(putTokens(null, self::$token_key));
		}
		
		$config 	= Config();
		$session 	= session();
		$data 		= collect($data ?: app(\Thujohn\Twitter\Twitter::class));
		
		if ($config->has('ttwitter::config')) {
			$this->tconfig = $config->get('ttwitter::config');
		} else if ($config->get('ttwitter')) {
			$this->tconfig = $config->get('ttwitter');
		} else {
			throw new RunTimeException('No config found');
		}
		
		$this->debug = (isset($this->tconfig['debug']) && $this->tconfig['debug']) ? true : false;
		
		$this->parent_config = [];
		$this->parent_config['consumer_key'] 	= $this->tconfig['CONSUMER_KEY'];
		$this->parent_config['consumer_secret'] = $this->tconfig['CONSUMER_SECRET'];
		$this->parent_config['token'] 			= $this->tconfig['ACCESS_TOKEN'];
		$this->parent_config['secret'] 			= $this->tconfig['ACCESS_TOKEN_SECRET'];
		
		if ($data->has('access_token')) {
			$access_token = $data->get('access_token');
			
			if (
				is_array($access_token) &&
				isset($access_token['oauth_token']) && isset($access_token['oauth_token_secret']) && !empty($access_token['oauth_token']) && !empty($access_token['oauth_token_secret'])
			) {
				$this->parent_config['token'] 	= $access_token['oauth_token'];
				$this->parent_config['secret'] 	= $access_token['oauth_token_secret'];
			}
			
		} else if ($session->has('access_token')) {
			$access_token = $session->get('access_token');
			
			if (
				is_array($access_token) &&
				isset($access_token['oauth_token']) &&
				isset($access_token['oauth_token_secret']) &&
				!empty($access_token['oauth_token']) &&
				!empty($access_token['oauth_token_secret'])
			) {
				$this->parent_config['token'] = $access_token['oauth_token'];
				$this->parent_config['secret'] = $access_token['oauth_token_secret'];
			}
		}
		
		$this->parent_config['use_ssl'] 	= $this->tconfig['USE_SSL'];
		$this->parent_config['user_agent'] 	= 'LTTW ' . parent::VERSION;
		
		$config = array_merge($this->parent_config, $this->tconfig);
		
		parent::__construct($this->parent_config);
	}
	
	/**
	 * Set new config values for the OAuth class like different tokens.
	 *
	 * @param array $config
	 *
	 * @return $this
	 */
	public function reconfig($config) {
		// The consumer key and secret must always be included when reconfiguring
		$config = array_merge($this->parent_config, $config);
		
		parent::reconfigure($config);
		
		return $this;
	}
	
	private function log($message) {
		if ($this->debug) {
			$this->log[] = $message;
		}
	}
	
	public function logs() {
		return $this->log;
	}
	
	/**
	 * Get a request_token from Twitter
	 *
	 * @param String|null $oauth_callback [Optional] The callback provided for Twitter's API. The user will be redirected there after authorizing your app on Twitter.
	 *
	 * @return array|Bool a key/value array containing oauth_token and oauth_token_secret in case of success
	 */
	public function getRequestToken($oauth_callback = null) {
		$parameters = [];
		
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		}
		
		parent::request('GET', parent::url($this->tconfig['REQUEST_TOKEN_URL'], ''), $parameters);
		
		$response = $this->response;
		
		if (isset($response['code']) && $response['code'] == 200 && !empty($response)) {
			$get_parameters = $response['response'];
			$token = [];
			parse_str($get_parameters, $token);
		}
		
		// Return the token if it was properly retrieved
		if (isset($token['oauth_token'], $token['oauth_token_secret'])) {
			return $token;
		} else {
			throw new RunTimeException($response['response'], $response['code']);
		}
	}
	
	/**
	 * Get an access token for a logged in user
	 *
	 * @returns array|Bool key/value array containing the token in case of success
	 */
	public function getAccessToken($oauth_verifier = null) {
		$parameters = [];
		
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}
		
		parent::request('GET', parent::url($this->tconfig['ACCESS_TOKEN_URL'], ''), $parameters);
		
		$response = $this->response;
		
		if (isset($response['code']) && $response['code'] == 200 && !empty($response)) {
			$get_parameters = $response['response'];
			$token = [];
			parse_str($get_parameters, $token);
			
			// Reconfigure the tmhOAuth class with the new tokens
			$this->reconfig([
				'token'  => $token['oauth_token'],
				'secret' => $token['oauth_token_secret'],
			]);
			
			return $token;
		}
		
		throw new RunTimeException($response['response'], $response['code']);
	}
	
	/**
	 * Get the authorize URL
	 *
	 * @returns string
	 */
	public function getAuthorizeURL($token, $sign_in_with_twitter = true, $force_login = false) {
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}
		
		if ($force_login) {
			return $this->tconfig['AUTHENTICATE_URL'] . "?oauth_token={$token}&force_login=true";
		} else if (empty($sign_in_with_twitter)) {
			return $this->tconfig['AUTHORIZE_URL'] . "?oauth_token={$token}";
		} else {
			return $this->tconfig['AUTHENTICATE_URL'] . "?oauth_token={$token}";
		}
	}
	
	public function query($name, $requestMethod = 'GET', $parameters = [], $multipart = false, $extension = 'json') {
		$this->config['host'] = $this->tconfig['API_URL'];
		
		if ($multipart) {
			$this->config['host'] = $this->tconfig['UPLOAD_URL'];
		}
		
		$url = parent::url($this->tconfig['API_VERSION'] . '/' . $name, $extension);
		
		$this->log('METHOD : ' . $requestMethod);
		$this->log('QUERY : ' . $name);
		$this->log('URL : ' . $url);
		$this->log('PARAMETERS : ' . http_build_query($parameters));
		$this->log('MULTIPART : ' . ($multipart ? 'true' : 'false'));
		
		parent::user_request([
			'method'    => $requestMethod,
			'host'      => $name,
			'url'       => $url,
			'params'    => $parameters,
			'multipart' => $multipart,
		]);
		
		$response = $this->response;
		
		$format = 'object';
		
		if (isset($parameters['format'])) {
			$format = $parameters['format'];
		}
		
		$this->log('FORMAT : ' . $format);
		
		$error = $response['error'];
		
		if ($error) {
			$this->log('ERROR_CODE : ' . $response['errno']);
			$this->log('ERROR_MSG : ' . $response['error']);
			
			$this->setError($response['errno'], $response['error']);
		}
		
		if (isset($response['code']) && ($response['code'] < 200 || $response['code'] > 206)) {
			$_response = $this->jsonDecode($response['response'], true);
			
			if (is_array($_response)) {
				if (array_key_exists('errors', $_response)) {
					$error_code = $_response['errors'][0]['code'];
					$error_msg = $_response['errors'][0]['message'];
				} else {
					$error_code = $response['code'];
					$error_msg = $response['error'];
				}
			} else {
				$error_code = $response['code'];
				$error_msg = ($error_code == 503) ? 'Service Unavailable' : 'Unknown error';
			}
			
			$this->log('ERROR_CODE : ' . $error_code);
			$this->log('ERROR_MSG : ' . $error_msg);
			
			$this->setError($error_code, $error_msg);
			
			throw new RunTimeException('[' . $error_code . '] ' . $error_msg, $response['code']);
		}
		
		switch ($format) {
			default :
			case 'object' :
				$response = $this->jsonDecode($response['response']);
				break;
			case 'json'   :
				$response = $response['response'];
				break;
			case 'array'  :
				$response = $this->jsonDecode($response['response'], true);
				break;
		}
		
		return $response;
	}
	
	public function get($name, $parameters = [], $multipart = false, $extension = 'json') {
		return $this->query($name, 'GET', $parameters, $multipart, $extension);
	}
	
	public function post($name, $parameters = [], $multipart = false) {
		return $this->query($name, 'POST', $parameters, $multipart);
	}
	
	public function linkify($tweet) {
		if (is_object($tweet)) {
			$type = 'object';
			$tweet = $this->jsonDecode(json_encode($tweet), true);
		} else if (is_array($tweet)) {
			$type = 'array';
		} else {
			$type = 'text';
			$text = ' ' . $tweet;
		}
		
		$patterns = [];
		$patterns['url'] = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
		$patterns['mailto'] = '([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))';
		$patterns['user'] = ' +@([a-z0-9_]*)?';
		$patterns['hashtag'] = '(?:(?<=\s)|^)#(\w*[\p{L}-\d\p{Cyrillic}\d]+\w*)';
		$patterns['long_url'] = '>(([[:alnum:]]+:\/\/)|www\.)?([^[:space:]]{12,22})([^[:space:]]*)([^[:space:]]{12,22})([[:alnum:]#?\/&=])<';
		
		if ($type == 'text') {
			// URL
			$pattern = '(?xi)\b((?:https?://|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))';
			$text = preg_replace_callback('#' . $patterns['url'] . '#i', function ($matches) {
				$input = $matches[0];
				$url = preg_match('!^https?://!i', $input) ? $input : "http://$input";
				
				return '<a href="' . $url . '" target="_blank" rel="nofollow">' . "$input</a>";
			}, $text);
		} else {
			$text = $tweet['text'];
			$entities = $tweet['entities'];
			
			$search = [];
			$replace = [];
			
			if (array_key_exists('media', $entities)) {
				foreach ($entities['media'] as $media) {
					$search[] = $media['url'];
					$replace[] = '<a href="' . $media['media_url_https'] . '" target="_blank">' . $media['display_url'] . '</a>';
				}
			}
			
			if (array_key_exists('urls', $entities)) {
				foreach ($entities['urls'] as $url) {
					$search[] = $url['url'];
					$replace[] = '<a href="' . $url['expanded_url'] . '" target="_blank" rel="nofollow">' . $url['display_url'] . '</a>';
				}
			}
			
			$text = str_replace($search, $replace, $text);
		}
		
		// Mailto
		$text = preg_replace('/' . $patterns['mailto'] . '/i', "<a href=\"mailto:\\1\">\\1</a>", $text);
		
		// User
		$text = preg_replace('/' . $patterns['user'] . '/i', " <a href=\"https://twitter.com/\\1\" target=\"_blank\">@\\1</a>", $text);
		
		// Hashtag
		$text = preg_replace('/' . $patterns['hashtag'] . '/ui', "<a href=\"https://twitter.com/search?q=%23\\1\" target=\"_blank\">#\\1</a>", $text);
		
		// Long URL
		$text = preg_replace('/' . $patterns['long_url'] . '/', ">\\3...\\5\\6<", $text);
		
		// Remove multiple spaces
		$text = preg_replace('/\s+/', ' ', $text);
		
		return trim($text);
	}
	
	public function ago($timestamp) {
		if (is_numeric($timestamp) && (int) $timestamp == $timestamp) {
			$carbon = Carbon::createFromTimeStamp($timestamp);
		} else {
			$dt = new \DateTime($timestamp);
			$carbon = Carbon::instance($dt);
		}
		
		return $carbon->diffForHumans();
	}
	
	public function linkUser($user) {
		return 'https://twitter.com/' . (is_object($user) ? $user->screen_name : $user);
	}
	
	public function linkTweet($tweet) {
		return $this->linkUser($tweet->user) . '/status/' . $tweet->id_str;
	}
	
	public function linkRetweet($tweet) {
		return 'https://twitter.com/intent/retweet?tweet_id=' . $tweet->id_str;
	}
	
	public function linkAddTweetToFavorites($tweet) {
		return 'https://twitter.com/intent/favorite?tweet_id=' . $tweet->id_str;
	}
	
	public function linkReply($tweet) {
		return 'https://twitter.com/intent/tweet?in_reply_to=' . $tweet->id_str;
	}
	
	public function error() {
		return $this->error;
	}
	
	public function setError($code, $message) {
		$this->error = compact('code', 'message');
		
		return $this;
	}
	
	private function jsonDecode($json, $assoc = false) {
		if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
			return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
		} else {
			return json_decode($json, $assoc);
		}
	}
	
	
	
	/**
	 * Get followers limit.
	 * followers/ids
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function getFollowersLimit($key = 'ids') {
		$d = $this->getRateLimit('followers', $key );
		if(count($d) == 1) {
			$d = $d[0];
		} else {
			$d = collect($d)->map(function ($e) {
				return (object) $e;
			})->toArray();
		}
		
		return (object) $d;
	}
	
	/**
	 * Get friends limit.
	 * friends/ides
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function getFriendsLimit($key = 'ids') {
		$d = $this->getRateLimit('friends', $key );
		if(count($d) == 1) {
			$d = $d[0];
		} else {
			$d = collect($d)->map(function ($e) {
				return (object) $e;
			})->toArray();
		}
		
		return (object) $d;
	}
	
	/**
	 * Get credentials limit.
	 * account/verify_credentials
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function getCredentialsLimit($key = 'verify_credentials') {
		$d = $this->getRateLimit('account', $key);
		if (count($d) == 1) {
			$d = $d[0];
		} else {
			$d = collect($d)->map(function ($e) {
				return (object) $e;
			})->toArray();
		}
		
		return (object) $d;
	}
	
	/**
	 * Get users/lookup limit.
	 * users/lookup
	 *
	 * @param string $key
	 *
	 * @return object
	 */
	public function getUsersLimit($key = 'lookup') {
		$d = $this->getRateLimit('users', $key);
		if (count($d) == 1) {
			$d = $d[0];
		} else {
			$d = collect($d)->map(function ($e) {
				return (object) $e;
			})->toArray();
		}
		
		return (object) $d;
	}
	
	public function getRateLimit($family = null, $key = null, $data = []) {
		$_family 	= $family ? ['resources' => $family] : [];
		$loaded 	= $this->loadAppRateLimit($family);
		
		$response = [];
		if($loaded && is_array($loaded)) {
			foreach ($loaded as $family=>$limit) {
				$limit = collect($limit);
				$limit = collect($limit->has('resources') ? $limit->get('resources') : $limit);

				if ($limit->count() && $family && $key) {
					$limit = collect($limit->get("/{$family}/{$key}", null));
				}
				
				$limit->put('family', $family ?: null);
				$limit->put('key', $key ?: null);
				$limit->put('api_family', "/" . $limit->get("family", '') . "/" . $limit->get('key', ''));
				
				$data['method'] = isset($data['method']) ? $data['method'] : debug_backtrace()[1]['function'];
				if (!$data['method']) {
					$data['method'] = $limit->get('api_family', null);
				} else {
					$limit->put('method', ($data['method'] ?: '') . ($key ? "@{$key}" : ""));
				}
				
				$response[] = $limit->toArray();
			}
			
			return $response;
		}
		
		return [];
	}
	
	/**
	 * Returns the current rate limits for methods belonging to the specified resource families.
	 * read rates limit from store or store it.
	 *
	 * store:
	 * 	self::$appRateLimitStore
	 */
	public function loadAppRateLimit($family = null) {
		if (is_null(self::$appRateLimitStore))
			self::$appRateLimitStore = collect([]);
		
		$limits = [];
		$_family = stripos($family, ',')!==false ? explode(',', $family) : [$family];
		foreach ($_family as $ii=>$RFamily) {
			if(!($RFamily = trim($RFamily))) continue;
			
			if (self::$appRateLimitStore->has($RFamily)) {
				$limits[$RFamily] = self::$appRateLimitStore->get($RFamily);
				
				unset($_family[$ii]);
			}
		}
		
		if(count($_family)) {
			$_family = implode(',', $_family);
			
			$_family 	= $_family ? ['resources' => $_family] : [];
			$__limits = $this->getAppRateLimit($_family);
			$__limits = $__limits->resources ?: null;
			$__limits = collect($__limits)->toArray();
			
			$limits = array_merge($limits, $__limits);
			
			if (count($__limits))
				self::$appRateLimitStore = self::$appRateLimitStore->merge($__limits);
		}
		
		return $limits;
	}
	
	
	/**
	 * Pull Followers from twitter, Push Followers to User entity.
	 *
	 * @param \App\User $user
	 *
	 * @return string
	 */
	public function getFollowers(User $user) {
		/**
		 * Time limit for timeout error.
		 */
		set_time_limit(43200);
		
		$cursor 			= -1; // first page
		$friend_total 		= 0;
		$twitter 			= $user->getTwitter();
		
		$acceptedFollowers = 0;
		while ($cursor != 0) {
			/*
			 * Pull friend/follower ID numbers, 100 at a time.
			 */
			$idsOptions = [
				'stringify_ids' => true,
				'count'         => 100,
				'cursor'        => $cursor,
			];
			
			$followers = $twitter->get("followers/ids", $idsOptions);
			if (!is_object($followers) || isset($followers->errors)) {
				return "Error retrieving followers: " . print_r($followers, 1);
			}
			
			$ids 			= implode(',', $followers->ids);
			$cursor 		= $followers->next_cursor_str;
			$friend_total  += count($followers->ids);
			
			/**
			 * Pull friend/follower details, 100 at a time, using POST.
			 */
			$usersData = [
				'user_id' 			=> $ids,
				'include_entities' => 'false',
				'tweet_mode'       => 'false'
			];
			// Pulling
			$users = $twitter->post("users/lookup", $usersData);
			
			if (!is_array($users)) {
				return "Error retrieving users: " . print_r($users, 1);
			}
			
			
			$acceptedFollowers += $user->pushFollowers($users);
			// try to avoid being rate limited
			sleep(2);
		}
		
		return $acceptedFollowers; //$user->followers;
		
		d(
			$user->followers()->count(),
			$user->followers
		);
	}
	
	/**
	 * Load & save Friends for specific user.
	 *
	 * @param \App\User $user
	 *
	 * @return string
	 */
	public function getFriends(User $user, callable $rowCallback = null) {
		/**
		 * Time limit for timeout error.
		 */
		set_time_limit(43200);
		
		$cursor 			= -1; // first page
		$friend_total 		= 0;
		$twitter 			= $user->getTwitter();
		$friendsFillabel 	= (new Friend)->getFillable();
		
		while ($cursor != 0) {
			/*
			 * Pull friend/follower ID numbers, 100 at a time.
			 */
			$idsOptions = [
				'stringify_ids' => true,
				'count'         => 100,
				'cursor'        => $cursor,
			];
			
			$friends = $twitter->get("friends/ids", $idsOptions);
			if (!is_object($friends) || isset($friends->errors)) {
				return "Error retrieving friends: " . print_r($friends, 1);
			}
			
			$ids 			= implode(',', $friends->ids);
			$cursor 		= $friends->next_cursor_str;
			$friend_total  += count($friends->ids);
			
			/**
			 * Pull friend/follower details, 100 at a time, using POST.
			 */
			$usersData = [
				'user_id' 			=> $ids,
				'include_entities'	=> 'false',
				'tweet_mode'		=> 'false'
			];
			$users = $twitter->post("users/lookup", $usersData);
			if (!is_array($users)) {
				return "Error retrieving users: " . print_r($users, 1);
			}
			
			// save friends/followers
			foreach ($users as $u) {
				$user->friends()->create(
					collect($u)->only($friendsFillabel)->toArray()
				);
			}
			
			// try to avoid being rate limited
			sleep(2);
		}
		
		
		d(
			$user->friends()->count(),
			$user->friends
		);
		
		return $user->friends;
	}
}