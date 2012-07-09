<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

/* Load OAuth lib. You can find it at http://oauth.net */
require_once(dirname(__FILE__) . '/facebook.php');
require_once(dirname(__FILE__) . '/../IOAuthProcess.php');

/**
 * Facebook OAuth class
 */
class FacebookOAuthClient extends Facebook implements IOAuthProcess {

	public $consumer;
	public $token;
	protected $callBackURL;
	protected $scopes;
	protected $oAuthDataStore;
	
	public function __construct($key, $secret, $tokenKey = NULL, $tokenSecret = NULL){
		parent::__construct(array(
		  'appId'  => $key,
		  'secret' => $secret,
		  'cookie' => true,
		));
		$this->consumer = new OAuthConsumer($key, $secret);
		$this->setAccessToken( $tokenKey );
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getRequestToken()
	*/
	public function fetchRequestToken($oauth_callback = NULL){ /* Do Nothing */ }

	/* (non-PHPdoc)
	 * @see BaseFacebook::getAccessToken()
	*/
	public function fetchAccessToken($oauth_verifier = NULL, $reqToken = NULL){
		$accTokenObj = (object) array(
				'error' => true,
				'oauth_key' => '',
				'oauth_secret' => '',
				'expires' => 0
		);		
		$fbToken = parent::getAccessToken();
		if( !empty($fbToken) ) {			
			$accTokenObj->oauth_key = $fbToken;
			$accTokenObj->oauth_secret = $reqToken;
			$accTokenObj->expires = 0;
			$accTokenObj->error = false;
		}
		return $accTokenObj;
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getURL()
	*/
	public function getAuthURL($action = self::ACTION_AUTHEMTICATE) {
		return $this->getLoginUrl(array(
			'scope' => implode(',', $this->getScope()),
			'redirect_uri' => $this->getCallback(),
		));
	}

	/* (non-PHPdoc)
	 * @see IOAuthProcess::getNetworkUser()
	*/
	public function getNetworkUser(){
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
			//Get the current user ID
			$networkUID = $this->getUser();
			//echo 'User ID: ' . $networkUID .'<br />';
			//Get the user based on the response
			if( $networkUID ) {
				$profileResp = (object) $this->api('/me');
				if( is_object($profileResp) ){
					$userObj->uid = $profileResp->id;
					$userObj->uname = $profileResp->username;
					$userObj->image = BaseFacebook::$DOMAIN_MAP['graph'] . "{$networkUID}/picture";
					$userObj->name = $profileResp->name;
					$userObj->profile = $profileResp->link;
					$userObj->valid = true;
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $userObj;
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
			$this->setAppId($this->consumer->key);
			$this->setAppSecret($this->consumer->secret);
		} else if( !empty($consumerOrKey) && !empty($consumerSecret) ) {
			$this->setConsumer( new OAuthConsumer($consumerOrKey, $consumerSecret) );
		}
	}
	
	/* (non-PHPdoc)
	 * @see IOAuthProcess::getCallback()
	*/
	public function getCallback() {
		return $this->callBackURL;
	}
	
	/* (non-PHPdoc)
	 * @see IOAuthProcess::setDataStore()
	 */
	public function setOAuthDataStore($newStore) {
		$this->oAuthDataStore = $newStore;
	}
	
	/* (non-PHPdoc)
	 * @see IOAuthProcess::setCallback()
	*/
	public function setCallback($newCallback) {
		$this->callBackURL = $newCallback;
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
	
}

