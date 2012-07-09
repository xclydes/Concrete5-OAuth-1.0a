<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::library('o_auth', XOAUTH_PKGHANDLE);
Loader::model('abs_x_o_auth_model', XOAUTH_PKGHANDLE);

abstract class AbsXOAuthToken extends AbsXOAuthModel {
	
	const TOKEN_REQUEST_OUT = 'request_out';
	const TOKEN_REQUEST = 'request_in';
	const TOKEN_ACCESS = 'access';
	
	public function setPropertiesFromArray($recordSet){
		try{
			parent::setPropertiesFromArray($recordSet);
			//Load the detail fields
			$this->key = $this->token;
			$this->secret = $this->secret;
		}catch(Exception $error){
			$this->handleError($error);
		}
	}

	public function getConsumer(){
		return XOAuthConsumer::getByKey($this->consumer_key);
	}
	
	public function setConsumer($newConsumer){
		if( $newConsumer instanceof OAuthConsumer ) {
			$this->consumerKey = $newConsumer->key;
		}else {
			$this->consumerKey = $newConsumer;
		}
	}
	
	public function setDetail($newDetail){
		$this->typeProperty = $newDetail;
	}
		
	public function toArray(){
		return array();
	}
	
	public function getUser(){
		return User::getByUserID($this->uID);
	}
	
	public function setUser($user){
		$this->verifiedById = $user instanceof User ?
			$user->getUserID() :
			$user;
	}
	
	public abstract function getType();	
}

