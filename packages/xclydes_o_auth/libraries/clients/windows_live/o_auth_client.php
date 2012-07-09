<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

/* Load OAuth lib. You can find it at http://oauth.net */
//require_once('OAuth.php');
require_once(dirname(__FILE__) . '/../IOAuthProcess.php');

/**
 * Twitter OAuth class
 */
class WindowsLiveOAuthClient implements IOAuthProcess {

	const AUTH_URL = 'https://oauth.live.com/authorize';
	const LIVEAPI_URL = 'https://apis.live.net/v5.0/';
	const TOKEN_URL = 'https://login.live.com/oauth20_token.srf';
	const PROFILE_URL = 'https://profile.live.com/cid-';

	protected $consumer;
	protected $token;
	protected $callBackURL;
	protected $scopes;
	protected $oAuthDataStore;

	function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		} else {
			$this->token = NULL;
		}
	}

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
			$this->consumer = new OAuthConsumer($consumerOrKey, $consumerSecret);
		}
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getURL()
	*/
	public function getAuthURL($action = self::ACTION_AUTHEMTICATE) {
		$url = '';
		//Get the scope
		$scope = rawurlencode(implode(' ', $this->getScope()));
		$url = self::AUTH_URL . '?' .
				"client_id={$this->getConsumer()->key}".
				"&scope={$scope}".
				"&response_type=code".
				'&redirect_uri=' . urlencode( $this->getCallback() );
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
			$creds = array ('access_token' => $this->token->key);
			//print_r($creds);
			//$userObj = (object) array();
			if( is_object($this->token) ) {
				$profileJSON = $this->getCURL(self::LIVEAPI_URL . 'me', $creds);
				$profileObj = json_decode (trim ($profileJSON));
				//echo 'Profile: ' . print_r($profileObj, true) . '<br />';
				if( is_object($profileObj)
						&& $profileObj->id ){
					$userObj->uid = $profileObj->id;
					$userObj->uname = $profileObj->name;
					$userObj->image = self::LIVEAPI_URL . 'me/picture/' . '?' . str_replace ('+', '%20', http_build_query ($creds, null, '&'));
					$userObj->name = $profileObj->name;
					$userObj->profile = $profileObj->link;
					$userObj->valid = true;
					//Get the user email if it is set
					if( is_object($profileObj->emails) ) {
						//Get the account email address
						$userObj->email = $profileObj->emails->account;
					}
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $userObj;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::setDataStore()
	*/
	public function setOAuthDataStore($newStore) {
		$this->oAuthDataStore = $newStore;
	}

	protected function handleError($error){
		if( is_object($error) ) {
			//Save the stack trace
			Log::addEntry($error->getMessage().'\n'.$error->getTraceAsString());
		}
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getRequestToken()
	*/
	public function fetchRequestToken($oauth_callback = NULL) { /* Do Nothing */	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getAccessToken()
	*/
	public function fetchAccessToken($verifier = NULL, $reqToken = NULL) {
		$accTokenObj = (object) array(
				'error' => true,
				'oauth_key' => '',
				'oauth_secret' => '',
				'expires' => 0
		);
		$content = array (
				'client_id' => $this->getConsumer()->key,
				'redirect_uri' => $this->getCallback(),
				'client_secret' => $this->getConsumer()->secret,
				'code' => $verifier,
				'grant_type' => 'authorization_code'
		);
		//Request the full token
		$response = $this->postCURL (self::TOKEN_URL, $content);
		//echo 'Response: ' . print_r($response, true);
		if ($response !== false) {
			$authToken = json_decode (trim ($response));
			if ( !empty ($authToken) ) {
				//print_r($authToken);
				$this->token = new OAuthConsumer(
						$authToken->access_token,
						$authToken->authentication_token
				);
				//print_r($this->token);
				$accTokenObj->oauth_key = $this->token->key;
				$accTokenObj->oauth_secret =$this->token->secret;
				$accTokenObj->expires = $authToken->expires_in + time();
				$accTokenObj->error = false;
			}
		}
		return $accTokenObj;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::refreshToken()
	*/
	public function refreshToken() {
		/* Nothing To Do */
	}

	/*public function getUser(){
		$creds = array ('access_token' => $this->token->key);
	//print_r($creds);
	$userObj = (object) array();
	if( is_object($this->token) ) {
	$user = $this->getCURL(self::LIVEAPI_URL . 'me', $creds);
	$userObj = json_decode (trim ($user));
	//print_r($userObj);
	//Add the user's image url
	if( is_object($userObj) ) {
	$userObj->image = self::LIVEAPI_URL . 'me/picture/' . '?' . str_replace ('+', '%20', http_build_query ($creds, null, '&'));
	$userObj->profile = self::PROFILE_URL . $userObj->id;
	}
	}
	return $userObj;
	}*/

	/**
	 * Send a HTTP POST request to a remote server, returns a string or false if there was an error
	 * @param string $url
	 * @param array $data
	 * @return mixed
	 */
	protected function postCURL ($url, $data = null) {
		//echo 'Connecting to ' . $url . '<br />';
		$ch = curl_init ();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_POST, 1);

		if ($data == null) {
			curl_setopt ($ch, CURLOPT_POSTFIELDS, null);
		} else {
			curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query ($data, null, '&'));
		}

		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);

		// CURL emits an error when it tries to validate the SSL certificate, we can prevent that by setting this option.
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$authData = curl_exec ($ch);

		//echo 'Curl Error: ' . curl_errno ($ch) . '<br />';
		if( (curl_errno ($ch) == 0)
			 && ( !empty ($authData) ) ) {
			curl_close ($ch);
			return $authData;
		}

		return false;
	}

	/**
	 * Send a HTTP GET request to a remote server, returns a string or false if there was an error
	 * @param string $url
	 * @param array $data
	 * @return mixed
	 */
	protected function getCURL ($url, $data = null)	{
		$ch = curl_init ();

		//echo 'URL: ' . $url . '. Data: ' . print_r($data, true) . '<br />';
		if ($data == null) {
			curl_setopt ($ch, CURLOPT_URL, $url);
		} else {
			curl_setopt ($ch, CURLOPT_URL, $url . '?' . str_replace ('+', '%20', http_build_query ($data, null, '&')));
		}

		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);

		// CURL emits an error when it tries to validate the SSL certificate, we can prevent that by setting this option.
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$authData = curl_exec ($ch);

		if ( (curl_errno ($ch) == 0)
			 && (empty ($authData) == false) ) {
			curl_close ($ch);
			return $authData;
		}

		return false;
	}

}

