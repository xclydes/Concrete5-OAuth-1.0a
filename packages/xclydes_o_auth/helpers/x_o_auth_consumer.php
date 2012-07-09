<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the oauth store
$pkgHandle = 'xclydes_o_auth';
Loader::model('x_config', $pkgHandle);
Loader::model('consumer_x_o_auth_store', $pkgHandle);
//Load the client libraries
Loader::library('clients/twitter/o_auth_client', $pkgHandle);
Loader::library('clients/facebook/o_auth_client', $pkgHandle);
Loader::library('clients/windows_live/o_auth_client', $pkgHandle);

class XOAuthConsumerHelper {

	const NETWORK_LOCAL = 'local';
	const NETWORK_TWITTER = 'twitter';
	const NETWORK_FACEBOOK = 'facebook';
	const NETWORK_WINDOWSLIVE = 'winlive';
	const NETWORK_LINKEDIN = 'linkedin';
	const NETWORK_GOOGLE = 'linkedin';

	const KEY_PUBLIC = 'key';
	const KEY_SECRET = 'secret';

	const ACTION_AUTHORIZE = 'authorize';
	const ACTION_AUTHENTICATE = 'authenticate';

	private static $_urls;
	private static $_clients;
	protected static $_pkgHandle= 'xclydes_o_auth';

	protected $_store;
	protected $config;
	protected $network;
	protected $accessTokenObj;

	public function __construct(){
		//Get the config
		$pkgConfig = new XConfig();
		$pkg = Package::getByHandle(self::$_pkgHandle);
		$pkgConfig->setPackageObject($pkg);
		$this->config = $pkgConfig;
	}

	public function setStore($store){
		$this->_store = $store;
		$this->network = is_object($this->_store) ?
		$this->_store->getNetwork() :
		'';
	}

	public function setAccessToken($token){
		$this->accessTokenObj = $token;
	}

	public function getRemoteNetworks(){
		return array(
				self::NETWORK_TWITTER => 'Twitter',
				self::NETWORK_FACEBOOK => 'Facebook',
				self::NETWORK_WINDOWSLIVE => 'Windows Live',
				self::NETWORK_LINKEDIN => 'Linked-In',
		);
	}

	public function getClient(){
		//Get the client generated previously
		$client = isset(self::$_clients[$this->network]) ?
		self::$_clients[$this->network] :
		null;
		if( !is_object($client)
				&& $this->network ) {
			//Get the api keys
			$consumerKey = $this->getConsumerKey(self::KEY_PUBLIC);
			$consumerSecret = $this->getConsumerKey(self::KEY_SECRET);
			$tokenKey = null;
			$tokenSecret = null;
			if( is_object($this->accessTokenObj) ) {
				$tokenKey = $this->accessTokenObj->key;
				$tokenSecret = $this->accessTokenObj->secret;
			}
			if( $consumerSecret && $consumerKey ){
				//Create the client based on the network selected
				switch($this->network){
					case self::NETWORK_WINDOWSLIVE:
						$client = new WindowsLiveOAuthClient($consumerKey, $consumerSecret, $tokenKey, $tokenSecret);
						break;
					case self::NETWORK_FACEBOOK:
						$client = new FacebookOAuthClient($consumerKey, $consumerSecret, $tokenKey, $tokenSecret);
						break;
					case self::NETWORK_TWITTER:
						$client = new TwitterOAuthClient($consumerKey, $consumerSecret, $tokenKey, $tokenSecret);
						break;
				}
			}
			//If the client was created
			if( is_object($client) ) {
				//Store the client for reference
				self::$_clients[$this->network] = $client;
				//Set the callback url
				$client->setCallback( $this->getCallbackUrl() );
				//Set the scope
				$client->setScope( $this->getNetworkScope() );
				//Set the data store
				$client->setOAuthDataStore( $this->_store );
			}
		}
		//echo 'Client: ' . print_r($client, true) . '<br />';
		return $client;
	}

	public function getProfile(){
		$profile = null;
		//Get the client
		$client = $this->getClient();
		if( $client instanceof IOAuthProcess ) {
			//Get the URL
			$profile = $client->getNetworkUser();
		}
		return is_object($profile) ? $profile : array('valid'=>false);
	}

	public function getConsumerKey($type){
		$prefKey = "xoauth.{$this->network}.consumer.{$type}";
		return $this->config->get($prefKey);
	}

	public function getNetworkScope() {
		return $this->config->get("xoauth.{$this->network}.consumer.scope", false, '');
	}

	public function getCallbackUrl(){
		$callbackUrl = $this->config->get('xoauth.consumer.destination.callback');
		if( $callbackUrl && $this->network ){
			//Append the trailing slash if necessary.
			if( substr($callbackUrl, strlen($callbackUrl) - 1 ) != '/'){
				$callbackUrl .= '/';
			}
			$redirectToken = '';//self::getRedirectToken();
			$callbackUrl .=  "{$this->network}";
			$callbackUrl = BASE_URL . View::url($callbackUrl);
		}else{
			$callbackUrl = '';
		}
		return $callbackUrl;
	}


	public function getUrl( $action = self::ACTION_AUTHENTICATE ){
		//See if the url was already generated
		$url = isset(self::$_urls[$this->network]) ?
		self::$_urls[$this->network] :
		null;
		//Use a url that was already created, if possible.
		//If none was found
		if( !$url ){
			//Get the client
			$client = $this->getClient();
			//echo 'Client: ' . print_r($client, true);
			if( $client instanceof IOAuthProcess ) {
				//Get the URL
				$url = $client->getAuthURL($action, $this->_store);
				//Store the url for future use.
				if( $url ){
					self::$_urls[$this->network] = $url;
				}
			}
		}
		return $url;
	}

	public function getAccessToken($verifier, $reqTokenKey){
		$accTokenObj = null;
		try{
			//Get the oauth client
			$client = $this->getClient();
			if( is_object($client) ) {
				$accTokenObj = $client->fetchAccessToken($verifier, $reqTokenKey);
				echo 'Token: ' . print_r($accTokenObj, true) . '<br />';
				//Ensure the token is valid before attempting to handle to it.
				if( is_object($accTokenObj)
						&& !$accTokenObj->error ) {
					//Get the user from the client
					$remoteProfile = $client->getNetworkUser();
					echo 'Profile: ' . print_r($remoteProfile, true) . '<br />';
					//exit();
					if( is_object( $remoteProfile ) ) {
						//Assume there is no user to login.
						$uID = 0;
						//Ensure this user was not previously registered
						$currAccToken = XOAuthAccessToken::getByRemoteID($this->network, $remoteProfile->uid);
						//Add the new token if it didnt exist
						if( !is_object($currAccToken) ) {
							//Get the auto create settings
							$autoCreate = $this->config->get('xoauth.user.new.auto.create', false, false);
							$userNameFormat = $this->config->get('xoauth.user.new.format', false, '%3$s_%2$s');
							//If there is no user logged in, and auto create is enable.
							if( !User::isLoggedIn()
									&& $autoCreate ){
								//Get the user email if available
								$userEmail = $remoteProfile->email ? $remoteProfile->email : uniqid();
								//Create the username based on the name format specified
								//%1$s - Screen Name, %2$s - Network UserID, %3$s - Network
								$newUserName = sprintf($userNameFormat, $remoteProfile->name, $remoteProfile->uid, $this->network);
								$data = array(
										'uName' => $newUserName,
										'uEmail' => $userEmail,//The user's email address.
										'uPassword' => uniqid(),//Random value for the password.
										'uIsValidated' => 1,//Mark as validated.
								);
								//Add the user
								Loader::model('userinfo');
								$newUserInfo = UserInfo::add($data);
								//Add the user to the group specified.
								$defaultUserGroup = $this->config->get('xoauth.user.new.group', false, '');
								if( $defaultUserGroup != '' ) {
									$newUserInfo->updateGroups(explode(',', $defaultUserGroup));
								}
								$newUserInfo->activate();
								//Update the uID value
								$uID = $newUserInfo->uID;
							} else  if( User::isLoggedIn() ) {
								//Get the current user
								$user = new User();
								//Default to the current user id.
								$uID = $user->getUserID();
							}
						} else {
							//Set the user to login as.
							$uID = $currAccToken->uID;
							if( $currAccToken->token != $accTokenObj->oauth_key
									|| $currAccToken->secret != $accTokenObj->oauth_secret ) {//Are the keys different?
								//Delete the old token
								$currAccToken->delete();
								//Save the updated token
								$currAccToken->save();
							}
						}
						//Add the token to the store
						XOAuthAccessToken::add(
								$this->network,
								$accTokenObj->oauth_key,
								$accTokenObj->oauth_secret,
								$uID,
								$remoteProfile->uid,
								$client->getConsumer()->key,
								$accTokenObj->expires
						);
						//print_r($currAccToken);
						//echo 'UID: '. $uID;
						//Login if configured to.
						if( $this->config->get('xoauth.user.new.auto.login', false, true)
								&& intval($uID) > 0 ) {
							User::loginByUserID( $uID );
						}
						//Mark the token as valid
						$accTokenObj->success = true;
					}
				}
			}
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $accTokenObj;
	}

	protected function handleError($error){
		if( is_object($error) ) {
			//Save the stack trace
			Log::addEntry($error->getMessage().'\n'.$error->getTraceAsString());
		}
	}
}
