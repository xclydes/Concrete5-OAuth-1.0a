<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('x_config', XOAUTH_PKGHANDLE);

class XoauthProviderController extends Controller {
	
	public function on_start(){
		parent::on_start();
		
		$pkgHandle = $this->getCollectionObject()->getPackageHandle();
		//Load the OAuth library
		Loader::model('provider_x_o_auth_store', $pkgHandle);
		//Setup the data store
		//Clean up the nonce and request tables.
		ProviderXOAuthStore::cleanup();
		$this->oauthStore = new ProviderXOAuthStore();
		$this->oauthServer = new OAuthServer( $this->oauthStore );
		$this->oauthServer->add_signature_method( new OAuthSignatureMethod_HMAC_SHA1() );
		$this->oauthServer->add_signature_method( new OAuthSignatureMethod_PLAINTEXT() );
		//Get the application config
		$pkgConfig = new XConfig();
		$pkg = Package::getByHandle( $pkgHandle );
		$pkgConfig->setPackageObject( $pkg );
		$this->config = $pkgConfig;
	}
	
	public function view(){
		//Display login options/tokens
	}	
	
	/**
	* XOAuth provider request & access token endpoint
	*/
	public function token( $type ){
		$response = null;
		//Process the request for the necessary information.
		$req = OAuthRequest::from_request();
		//Determine the type of token being worked with
		switch( $type ) {
			case 'access':
				//Generate a token for the request
				$token = $this->oauthServer->fetch_access_token($req);
				//Get the string representing the token
				$response = $token->to_string();
			break;
			case 'request':
				//Generate a token for the request
				$token = $this->oauthServer->fetch_request_token($req);
				//Get the string representing the token
				$response = $token->to_string();
			break;
		}
		//Output wat was generated
		echo $response;
		//Do not add any other information
		exit(0);
	}
	
	/**
	* Allows a user to be authenticated using
	* their username and password.
	*/
	public function direct(){
		$verifier = '';
		try{
			//Get the request token
			$reqToken = $this->oauthStore->lookup_token(null, AbsXOAuthToken::TOKEN_REQUEST, $this->post('oauth_token'));
			//Get the username posted
			$username = $this->post('username');
			//Get the password posted
			$password = $this->post('password');
			//Attempt to get the user submitted
			$user = new User($username, $password);
			if( $user 
				&& $user->checkLogin()
				&& $reqToken ){
				//Mark the token as verified
				$reqToken->markAsVerified();
				//Return the verifier
				$verifier = $reqToken->getVerifier();
			}
		}catch(Exception $error){
			$this->handleError($error);
		}		
		//Print the verifier
		echo $verifier;
		//Do not add any other information
		exit(0);
	}

}
