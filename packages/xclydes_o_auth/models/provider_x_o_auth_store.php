<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::library('o_auth', XOAUTH_PKGHANDLE);
Loader::model('x_o_auth_consumer', XOAUTH_PKGHANDLE);
Loader::model('x_o_auth_access_token', XOAUTH_PKGHANDLE);
Loader::model('provider_x_o_auth_request_token', XOAUTH_PKGHANDLE);

class ProviderXOAuthStore extends OAuthDataStore{

	const DATE_SQLDATE = "Y-m-d";
	const DATE_SQLTS = "Y-m-d G:i:s";
	
	const NONCE_AGE = 600;
	const DBTABLE_NONCE = 'btxoauthnonce';
	const DBTABLE_ACCESS = 'btxoauthtokens';
	const DBTABLE_REQUEST_IN = 'btxoauthrequestslocal';
	const DBTABLE_REQUEST_OUT = 'btxoauthrequestsremote';
	const DBTABLE_CONSUMER = 'btxoauthconsumers';
		
	protected $network = 'local';
	
	public function __construct(){
		//Clean up the store
		self::cleanup();
	}
			
	protected static function handleError($error){
		if( is_object($error) ) { 
			//Save the stack trace
			Log::addEntry($error->getMessage().'\n'.$error->getTraceAsString());
		}
	}
	
	public function lookup_consumer($consumer_key) {
		//Consumer's are network independent, local only.
		return XOAuthConsumer::getByKey($consumer_key);
	}
	
	public function lookup_token($consumer, $token_type, $token) {
		$tokenFound = null;
		$removeToken = false;
		try{
			switch( $token_type ) {
				case AbsXOAuthToken::TOKEN_ACCESS:
					$tokenFound = XOAuthAccessToken::getByToken($token, $this->network);
				break;
				case AbsXOAuthToken::TOKEN_REQUEST:
					$tokenFound = ProviderXOAuthRequestToken::getByToken($token, $this->network);
				break;
			}
		} catch (Exception $e) {
			self::handleError($e);
		}
		return $tokenFound;
	}
	
	public function lookup_nonce($consumer, $token, $nonce, $timestamp) {
		try{
			//Clean up the nonce table
			self::cleanup();
			$db = Loader::db();
			//Ensure the nonce passed doesnt exist.
			$searchQuery = 'SELECT nonce FROM ' . self::DBTABLE_NONCE . ' WHERE nonce = ?';
			$searchResults = $db->Execute($searchQuery, array($nonce));
			if( $searchResults->RecordCount() <= 0 ) { //The record does not exist.
				//Add the nonce passed.
				$insertQuery = 'INSERT INTO ' . self::DBTABLE_NONCE . '(nonce,generated) VALUES(?,?)';
				$db->Execute($insertQuery, array($nonce, date(self::DATE_SQLTS)));
				$nonce = NULL;
			} else {//It has been used
				//Let it be known
			}
		} catch (Exception $e) {
			self::handleError($e);
		}
		return $nonce;
	}
	
	public function new_request_token($consumer, $callback = null) {
		$tokenObj = null;
		try{
			//Generate the token
			$user = new User();
			$tokenStr = md5($user->getUserID() . date(AbsXOAuthModel::DATE_DESCRIPTIVE_LONG));
			//Geneate the secret
			$secret = hash('ripemd160', mt_rand() . $tokenStr);
			//Create the new token
			$tokenObj = new ProviderXOAuthReqToken();
			$tokenObj->network = 'local';
			$tokenObj->callback_url = $callback;
			$tokenObj->key = $token;
			$tokenObj->token = $token;
			$tokenObj->secret = $secret;
			//Save the token generated
			$tokenObj->save();
		} catch (Exception $e) {
			self::handleError($e);
		}	
		return $tokenObj;	
	}
	
	public function new_access_token($token, $consumer, $verifier = null) {
		$tokenObj = null;
		try{
			$network = 'local';
			if( $verifier != null ){
				//Lookup the request token specified
				$reqToken = $this->lookup_token($consumer, AbsXOAuthToken::TOKEN_REQUEST, $token->key);
				if( $reqToken != null 
					&& $verifier != null){
					//Get the user who authorized the request
					$verifiedBy = $reqToken->getVerifyingUser();
					//Detemine if an entry exists for this consumer and user combination
					$localUserAccessTokens = XOAuthAccessToken::getForUser($network, $verifiedBy->getUserID());
					//There is none
					if( empty($localUserAccessTokens) ){
						//Generate the access token
						$token = sha1($verifiedBy->getUserID() . date(self::DATE_SQLTS));
						//Geneate the secret
						$secret = hash('tiger160,4', mt_rand() . $token);
						//Create the new token
						$tokenObj = new XOAuthAccessToken($consumer, $token, $secret);
						$tokenObj->network = $network;
						$tokenObj->token = $token;
						$tokenObj->key = $token;
						$tokenObj->secret = $secret;
						$tokenObj->consumer_key = $consumer;
						$tokenObj->uID = $verifiedBy->getUserID();						
						//Save the token generated
						$tokenObj->save();
					}else{
						//Take the first token only
						$tokenObj = array_pop($localUserAccessTokens);
					}
				}
			}
		} catch (Exception $e) {
			self::handleError($e);
		}	
		return $tokenObj;	
	}
	
	public static function new_nonce(){
		//Get the current user id
		$user = new User();
		$uId = $user->getUserID();
		//Generate the new nonce
		return md5( time() + ';' +  mt_rand() + ';' + $uId );
	}	
	
	public static function cleanup(){
		try{
			$db = Loader::db();
			//Get the current timestamp
			$time = time() - self::NONCE_AGE;
			$expiryTs = date(self::DATE_SQLTS, $time);
			//Remove all nonce over a certain age.
			$db->Execute('DELETE FROM '.self::DBTABLE_NONCE." WHERE generated < '{$expiryTs}'");
			//Remove all requests over a certain age
			$db->Execute('DELETE FROM '.self::DBTABLE_REQUEST_IN." WHERE generated < '{$expiryTs}' AND verified IS NULL");
			$db->Execute('DELETE FROM '.self::DBTABLE_REQUEST_OUT." WHERE generated < '{$expiryTs}'");/**/
		}catch(Exception $error){
			self::handleError($error);
		}
	}
} 
