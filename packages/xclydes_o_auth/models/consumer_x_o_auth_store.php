<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::library('o_auth', XOAUTH_PKGHANDLE);
Loader::model('x_o_auth_consumer', XOAUTH_PKGHANDLE);
Loader::model('x_o_auth_access_token', XOAUTH_PKGHANDLE);
Loader::model('provider_x_o_auth_store', XOAUTH_PKGHANDLE);
Loader::model('consumer_x_o_auth_request_token', XOAUTH_PKGHANDLE);

class ConsumerXOAuthStore extends ProviderXOAuthStore{
	
	public function __construct($network){
		$this->setNetwork($network);
	}
	
	public function setNetwork($network){
		$this->network = $network;
	}
	
	public function getNetwork(){
		return $this->network;
	}
	
	public function lookup_token($consumer, $token_type, $token) {
		$tokenFound = null;
		try{
			switch( $token_type ) {
				case AbsXOAuthToken::TOKEN_REQUEST_OUT:
					$tokenFound = ConsumerXOAuthRequestToken::getByToken($token, $this->network);
				break;
				default:
					$tokenFound = parent::lookup_token($consumer, $token_type, $token);
				break;
			}
		} catch (Exception $e) {
			self::handleError($e);
		}
		return $tokenFound;
	}
	
	public function new_request_token($consumer, $callback = null) {
		$tokenObj = null;
		try{
			$arrHelper = Loader::helper('array');
			$token = $arrHelper->get($consumer, 'oauth_token', null);
			$secret = $arrHelper->get($consumer, 'oauth_token_secret', null);
			if( $token && $secret ){
				//Setup the token
				$tokenObj = new ConsumerXOAuthRequestToken();
				$tokenObj->network = $this->network;
				$tokenObj->callback_url = $callback;
				$tokenObj->key = $token;
				$tokenObj->token = $token;
				$tokenObj->secret = $secret;
				//Save the token generated
				$tokenObj->save();
			}
		} catch (Exception $e) {
			self::handleError($e);
		}	
		return $tokenObj;	
	}
	
	//Not applicable in this case
	public function new_access_token($token, $consumer, $verifier = null) {
		$tokenObj = null;
	}
	
} 
