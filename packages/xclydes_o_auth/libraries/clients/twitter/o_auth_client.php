<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

/*
 * Abraham Williams (abraham@abrah.am) http://abrah.am
*
* The first PHP Library to support OAuth for Twitter's REST API.
*/

/* Load OAuth lib. You can find it at http://oauth.net */
//require_once('OAuth.php');
require_once(dirname(__FILE__) . '/../IOAuthProcess.php');

/**
 * Twitter OAuth class
 */
class TwitterOAuthClient implements IOAuthProcess {
	/* Contains the last HTTP status code returned. */
	public $http_code;
	/* Contains the last API call. */
	public $url;
	/* Set up the API root URL. */
	public $host = "https://api.twitter.com/1/";
	/* Set timeout default. */
	public $timeout = 30;
	/* Set connect timeout. */
	public $connecttimeout = 5;
	/* Verify SSL Cert. */
	public $ssl_verifypeer = FALSE;
	/* Respons format. */
	public $format = 'json';
	/* Decode returned json data. */
	public $decode_json = TRUE;
	/* Contains the last HTTP headers returned. */
	public $http_info;
	/* Set the useragnet. */
	public $useragent = 'TwitterOAuth v0.2.0-beta2';
	/* Immediately retry the API call if the response was not successful. */
	//public $retry = TRUE;

	/**
	 * Jermaine Rattray (xclydes) - June.12.2012 21.55 -5
	 * Additional properties to comply with IOAuthProcess interface.
	 */

	public $consumer;
	public $token;
	protected $callBackURL;
	protected $scopes;
	protected $oAuthDataStore;

	/* Emd Change */

	/**
	 * Set API URLS
	 */
	function accessTokenURL()  {
		return 'https://api.twitter.com/oauth/access_token';
	}
	function authenticateURL() {
		return 'https://twitter.com/oauth/authenticate';
	}
	function authorizeURL()    {
		return 'https://twitter.com/oauth/authorize';
	}
	function requestTokenURL() {
		return 'https://api.twitter.com/oauth/request_token';
	}

	/**
	 * Debug helpers
	 */
	function lastStatusCode() {
		return $this->http_status;
	}
	function lastAPICall() {
		return $this->last_api_call;
	}

	/**
	 * construct TwitterOAuth object
	 */
	function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		} else {
			$this->token = NULL;
		}
	}

	/**
	 * Jermaine Rattray (xclydes) - June.12.2012 21.55 -5
	 * Additional methods to comply with IOAuthProcess interface.
	 */

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getConsumer()
	*/
	public function getConsumer(){
		return $this->consumer;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::setConsumer()
	*/
	public function setConsumer($consumerOrKey, $consumerSecret = NULL) {
		if( $consumerOrKey instanceof OAuthConsumer ) {
			$this->consumer = $consumerOrKey;
		} else if( !empty($consumerOrKey) && !empty($consumerSecret) ) {
			$this->setConsumer( new OAuthConsumer($consumerOrKey, $consumerSecret) );
		}
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::setDataStore()
	*/
	public function setOAuthDataStore($newStore) {
		$this->oAuthDataStore = $newStore;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getURL()
	*/
	public function getAuthURL($action = self::ACTION_AUTHEMTICATE) {
		$url = '';
		//Get the token to be used
		try {
			//Load the array helper
			$arrayHelper = Loader::helper('array');
			//Get the request token
			$request_token = $this->fetchRequestToken();
			$oauthToken = $arrayHelper->get($request_token, 'oauth_token', NULL);
			//Store the token for reference
			if( $this->oAuthDataStore != null ) {
				//Add the request token to the store.
				$this->oAuthDataStore->new_request_token($request_token, $this->getCallback());
			}
			if( !empty($oauthToken) ) {
				switch ( $action ) {
					case self::ACTION_AUTHORIZE:
						$url = $this->authorizeURL() . "?oauth_token={$oauthToken}";
						break;
					case self::ACTION_AUTHEMTICATE:
					default:
						$url = $this->authenticateURL() . "?oauth_token={$oauthToken}";
						break;
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $url;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getCallback()
	*/
	public function getCallback() {
		return $this->callBackURL;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::setCallback()
	*/
	public function setCallback($newCallback) {
		$this->callBackURL = $newCallback;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getNetworkUser()
	*/
	public function getNetworkUser() {
		$userObj = (object) array(
				'uid' => '',
				'uname' => '',
				'image' => '',
				'name' => '',
				'profile' => '',
				'email' => '',
				'valid' => false,
		);
		//Get the user from the API
		try {
			$profileResp = $this->get('account/verify_credentials');
			if( is_object($profileResp) ){
				$userObj->uid = $profileResp->id_str;
				$userObj->uname = $profileResp->screen_name;
				$userObj->image = $profileResp->profile_image_url_https;
				$userObj->name = $profileResp->name;
				$userObj->profile = "https://twitter.com/{$profileResp->screen_name}";
				$userObj->valid = true;
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $userObj;
	}

	protected function handleError($error){
		if( is_object($error) ) {
			//Save the stack trace
			Log::addEntry($error->getMessage().'\n'.$error->getTraceAsString());
		}
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getScope()
	*/
	public function getScope() {
		//If no scope is set
		if( $this->scopes == null ) {
			//Use an empty array
			$this->scopes = array();
		}
		return $this->scopes;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::setScope()
	*/
	public function setScope($newScope) {
		//Initialize the current scope
		$this->getScope();
		if( is_string($newScope)
				&& !empty($newScope) ) {
			//Clear the current scope
			$this->scopes = array($newScope);
		} else if( is_array($newScope) ) {
			$this->scopes = $newScope;
		} else {
			//Clear the scopes
			$this->scopes = null;
		}
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::refreshToken()
	*/
	public function refreshToken() {
		/* Nothing To Do */
	}

	/* Emd Change */

	/**
	 * Get a request_token from Twitter
	 *
	 * @returns a key/value array containing oauth_token and oauth_token_secret
	 */
	function fetchRequestToken() {
		$parameters = array('oauth_callback' => $this->getCallback());
		$request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * Exchange request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @returns array("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham")
	 */
	function fetchAccessToken($oauth_verifier = FALSE, $reqTokenKey = NULL) {
		$accTokenObj = (object) array(
			'error' => true,
			'oauth_key' => '',
			'oauth_secret' => '',
			'expires' => 0
		);
		if( is_object($this->oAuthDataStore) ) {			
			//Get the request token specified from the store
			$requestToken = $this->oAuthDataStore->lookup_token(null, AbsXOAuthToken::TOKEN_REQUEST_OUT, $reqTokenKey);
			//echo 'Req Token: ' . $reqTokenKey .' => ' . print_r($requestToken, true);
			if( is_object($requestToken) ){
				//Set the request token as the token to use
				$this->token = new OAuthConsumer($requestToken->token, $requestToken->secret);
				$parameters = array();
				//Set the verifier parameter
				if (!empty($oauth_verifier)) {
					$parameters['oauth_verifier'] = $oauth_verifier;
				}
				//Perform the request
				$request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
				//Parse the response
				$token = OAuthUtil::parse_parameters($request);
				//print_r($token);
				if( !empty($token) ) {
					$accTokenObj->oauth_key = $token['oauth_token'];
					$accTokenObj->oauth_secret = $token['oauth_token_secret'];
					$accTokenObj->expires = 0;
					$accTokenObj->error = false;
					//Set the token to be used
					$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
				}
			}
		}
		return $accTokenObj;
	}

	/**
	 * One time exchange of username and password for access token and secret.
	 *
	 * @returns array("oauth_token" => "the-access-token",
	 *                "oauth_token_secret" => "the-access-secret",
	 *                "user_id" => "9436992",
	 *                "screen_name" => "abraham",
	 *                "x_auth_expires" => "0")
	 */
	function getXAuthToken($username, $password) {
		$parameters = array();
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}

	/**
	 * GET wrapper for oAuthRequest.
	 */
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * POST wrapper for oAuthRequest.
	 */
	function post($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * DELETE wrapper for oAuthReqeust.
	 */
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * Format and sign an OAuth / API request
	 */
	function oAuthRequest($url, $method, $parameters) {
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}
		$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
			case 'GET':
				return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @return API results
	 */
	function http($url, $method, $postfields = NULL) {
		$response = null;
		try {
			$this->http_info = array();
			$ci = curl_init();
			/* Curl settings */
			curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
			curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
			curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
			curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
			curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
			curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
			curl_setopt($ci, CURLOPT_HEADER, FALSE);
	
			switch ($method) {
				case 'POST':
					curl_setopt($ci, CURLOPT_POST, TRUE);
					if (!empty($postfields)) {
						curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
					}
					break;
				case 'DELETE':
					curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
					if (!empty($postfields)) {
						$url = "{$url}?{$postfields}";
					}
			}
	
			curl_setopt($ci, CURLOPT_URL, $url);
			$response = curl_exec($ci);
			$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
			$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
			$this->url = $url;
			curl_close ($ci);
		} catch(Exception $error) {
			$this->handleError($error);
		}
		return $response;
	}

	/**
	 * Get the header info to store.
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}
