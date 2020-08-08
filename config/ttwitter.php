<?php
// You can find the keys here : https://apps.twitter.com/

define('TOKENS', [
	'DEFAULT' => 'localhost',
	'TOKENS' => [

		'localhost' => [
			'CONSUMER_KEY'        => 'Nx2LBgK7kGTlhfSfPY9HEcvfK',
			'CONSUMER_SECRET'     => 'WG68LlnbVLnX1OuhzGEOmeFa9aRkojL7LCyByX6h4x3XagJVGj',
			'ACCESS_TOKEN'        => '','ACCESS_TOKEN_SECRET' => '',
		],

		'hlack.xyz' => [
			'CONSUMER_KEY'        => 'Nx2LBgK7kGTlhfSfPY9HEcvfK',
			'CONSUMER_SECRET'     => 'WG68LlnbVLnX1OuhzGEOmeFa9aRkojL7LCyByX6h4x3XagJVGj',
			'ACCESS_TOKEN'        => '','ACCESS_TOKEN_SECRET' => '',
		],

		'decodercan.com' => [
			'CONSUMER_KEY'        => 'SztzVXW7cCAiWMSkaN8wSkKiC',
			'CONSUMER_SECRET'     => '88f94XbHltTb6LGrFPAiDYlCjfGLp8lzPBh14fjHcKqTyZd9vB',
			'ACCESS_TOKEN'        => '','ACCESS_TOKEN_SECRET' => '',
		],
	],
]);

function putTokens($key = null, $token_key = null) {
	$token_key = $token_key ?: TOKENS['DEFAULT'];
	$data 	= $token_key ? TOKENS['TOKENS'][$token_key] : TOKENS['TOKENS'] ;

	if($key == 'DEFAULT_TOKEN')
		return TOKENS['DEFAULT'];

	if(!$key) {
		return $data;
	} else if(isset($data[$key]) || $key == 'DEFAULT_TOKEN') {
		return $data[$key];
	}

	return null;
}


return [
	'debug'               => function_exists('env') ? env('APP_DEBUG', false) : false,

	'API_URL'             => 'api.twitter.com',
	'UPLOAD_URL'          => 'upload.twitter.com',
	'API_VERSION'         => '1.1',
	'AUTHENTICATE_URL'    => 'https://api.twitter.com/oauth/authenticate',
	'AUTHORIZE_URL'       => 'https://api.twitter.com/oauth/authorize',
	'ACCESS_TOKEN_URL'    => 'https://api.twitter.com/oauth/access_token',
	'REQUEST_TOKEN_URL'   => 'https://api.twitter.com/oauth/request_token',
	'USE_SSL'             => true,


	'DEFAULT_TOKEN'       => putTokens('DEFAULT_TOKEN'),
	'CONSUMER_KEY'        => putTokens('CONSUMER_KEY'),
	'CONSUMER_SECRET'     => putTokens('CONSUMER_SECRET'),
	'ACCESS_TOKEN'        => putTokens('ACCESS_TOKEN'),
	'ACCESS_TOKEN_SECRET' => putTokens('ACCESS_TOKEN_SECRET'),
];






// TWITTER_CONSUMER_KEY = Nx2LBgK7kGTlhfSfPY9HEcvfK
// TWITTER_CONSUMER_SECRET = WG68LlnbVLnX1OuhzGEOmeFa9aRkojL7LCyByX6h4x3XagJVGj
// TWITTER_ACCESS_TOKEN =
// TWITTER_ACCESS_TOKEN_SECRET =
//
// TWITTER_CONSUMER_KEY2 = SztzVXW7cCAiWMSkaN8wSkKiC
// TWITTER_CONSUMER_SECRET2 = 88f94XbHltTb6LGrFPAiDYlCjfGLp8lzPBh14fjHcKqTyZd9vB
// TWITTER_ACCESS_TOKEN2 =
// TWITTER_ACCESS_TOKEN_SECRET2 =


// 'CONSUMER_KEY'        => function_exists('env') ? env('TWITTER_CONSUMER_KEY', '') : '',
// 'CONSUMER_SECRET'     => function_exists('env') ? env('TWITTER_CONSUMER_SECRET', '') : '',
// 'ACCESS_TOKEN'        => function_exists('env') ? env('TWITTER_ACCESS_TOKEN', '') : '',
// 'ACCESS_TOKEN_SECRET' => function_exists('env') ? env('TWITTER_ACCESS_TOKEN_SECRET', '') : '',


