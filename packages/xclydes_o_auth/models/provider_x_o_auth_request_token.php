<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::model('abs_x_o_auth_token', XOAUTH_PKGHANDLE);

class ProviderXOAuthRequestToken extends AbsXOAuthToken {
	
	const FIELD_KEY = 'token';
	const TABLE_NAME = 'btxoauthrequestslocal';
	
	const OOB_CALLBACK = 'http://oob/';
	
	protected $verifiedByUser;
	
	protected static function getBaseQuery(){
		return 'SELECT a.* 
		FROM ' . self::TABLE_NAME . ' AS a';
	}

	public static function getByToken($reqToken){
		$token = null;
		try{
			//The network is always local when dealing with the provider
			$network = 'local';
			//Get the base query
			$query = self::getBaseQuery();
			//Added the conditions
			$query .= ' WHERE a.status > 0 AND a.'.self::FIELD_KEY.' = ? AND a.network = ?';
			//Execute the query
			$db = Loader::db();
			$recordSet = $db->Execute($query, array($reqToken, $network));
			//Convert the results to tokens
			$reqTokens = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, null);
			if( !empty($reqTokens) ){
				//Select the token
				$token = array_pop( $reqTokens );
			}
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $token;
	}
	
	/**
	* Gets representation of the type of token.
	*/
	public function getType(){
		return self::TOKEN_REQUEST;
	}
	
	/**
	* Updates the details of the token to
	* indicate a user's details have been presented.
	*/
	public function markAsVerified(){
		if( !$this->isVerified() ){
			$verifierSize = 6;
			$combinedKey = rand() . rand() . rand();
			$startIndex = strlen($combinedKey) / 4;
			//Set the verifier
			$this->verifier = substr($combinedKey, $startIndex, $verifierSize);
			//Set the verification date
			$this->verified = date(self::DATE_SQLTS);
			//Set the user
			$this->setVerifyingUser(new User());
			//Update the record
			$this->save();
		}
	}
	
	public function isVerified(){
		return $this->verified != null;
	}
	
	/**
	* Gets the callback URL specified 
	* as the callback.
	*/
	public function getCallBack(){
		return $this->callback_url;
	}
	
	public function isOOB(){
		return $this->getCallBack() == self::OOB_CALLBACK;
	}

	public function save(){
		try{
			//Execute the query
			$db = Loader::db();
			//It is an update
			if( $this->isVerified() ){
				$query = "UPDATE ".self::TABLE_NAME.
						" SET uID = ?,
							consumer_key = ?,
							network = ?,
							callback_url = ?,
							verifier = ?,
							verified = ?,
							status = ?,
							generated = ?
						WHERE " . self::FIELD_KEY . " = ? AND network = ? AND secret = ?";
				$props = array($this->uID,$this->consumer_key,$this->network,$this->callback_url,$this->verifier,$this->verified,$this->status,$this->generated,$this->token,$this->network,$this->secret);
				$db->Execute($query, $props);
			}else{
				//Attempt to insert the value
				$query = "INSERT INTO ".self::TABLE_NAME."(uID,consumer_key,callback_url,verifier,verified,status,generated," . self::FIELD_KEY . ",secret,network) VALUES(?,?,?,?,?,?,?,?,?,?)";
				$props = array($this->uID,$this->consumer_key,$this->callback_url,$this->verifier,$this->verified,$this->status,$this->generated,$this->token,$this->secret,$this->network);
				$db->Execute($query, $props);
			}
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $this->token;
 	}
}
