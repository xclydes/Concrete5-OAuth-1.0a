<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::model('abs_x_o_auth_token', XOAUTH_PKGHANDLE);

class XOAuthAccessToken extends AbsXOAuthToken {
	
	const FIELD_KEY = 'token';
	const TABLE_NAME = 'btxoauthtokens';

	public function getType(){
		return self::TOKEN_ACCESS;
	}

	protected static function getBaseQuery(){
		return 'SELECT a.* 
		FROM ' . self::TABLE_NAME . ' AS a';
	}
	
	public static function add($network, $key, $secret, $localUID, $remoteUID = null, $consumerKey = null, $expires = 0){
		try {
			//Attempt to insert the values. It will fail if already exists			
			$query = "INSERT INTO " . self::TABLE_NAME . "(network,token,secret,uID,consumer_key,remote_uID,generated,expires) VALUES(?,?,?,?,?,?,?,?)";
			$params = array($network,$key,$secret,$localUID,$consumerKey,$remoteUID, self::formatDate(time(), self::DATE_SQLTS), self::formatDate($expires, self::DATE_SQLTS));
			$db = Loader::db();
			$db->Execute($query, $params);
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
	}
	
	public static function getByRemoteID($network, $remouteUID){
		$existingToken = null;
		try{
			//Get the base query
			$query = self::getBaseQuery();
			//Added the conditions
			$query .= ' WHERE a.remote_uID = ? AND LOWER(a.network) = LOWER(?)';
			//Execute the query
			$db = Loader::db();
			$recordSet = $db->Execute($query, array($remouteUID, $network));
			//Convert the results to vehicles
			$accTokens = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, null);
			$existingToken = !empty($accTokens) ?
				array_pop($accTokens) :
				null;
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $existingToken;
	}
	
	public static function getForUser($network = '%', $userID = 0){
		$accTokens = null;
		try{
			if( $userID < 1 ){
				//Search using the current user
				$user = new User();
				$userID = $user->getUserID();
			}
			//Get the base query
			$query = self::getBaseQuery();
			//Added the conditions
			$query .= ' WHERE a.uID = ? AND LOWER(a.network) LIKE LOWER(?)';
			//Execute the query
			$db = Loader::db();
			$recordSet = $db->Execute($query, array($userID, $network));
			//Convert the results to vehicles
			$accTokens = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, null);
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $accTokens;
	}

	public static function getByToken($accToken,$network){
		$token = null;
		try{
			//Get the base query
			$query = self::getBaseQuery();
			//Added the conditions
			$query .= ' WHERE a.status > 0 AND a.'.self::FIELD_KEY.' = ? AND a.netowrk = ?';
			//Execute the query
			$db = Loader::db();
			$recordSet = $db->Execute($query, array($accToken, $network));
			//Convert the results to vehicles
			$accTokens = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, null);
			if( !empty($accTokens) ){
				//Select the vehicle
				$token = array_pop( $accTokens );
			}
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $token;
	}
	
	public function delete() {
		try{
			//Execute the query
			$db = Loader::db();
			//Delete the key from the database
			$query = "DELETE FROM ".self::TABLE_NAME." WHERE " . self::FIELD_KEY . " = ? AND network = ? AND secret = ?";
			$props = array($this->token,$this->network,$this->secret);
			$db->Execute($query, $props);			
		}catch(Exception $error){
			$this->handleError($error);
		}
	}
	
	public function save(){
		try{
			//Execute the query
			$db = Loader::db();
			$expiry = self::formatDate($this->expires, self::DATE_SQLTS);
			//It is an update
			if( $this->generated ){
				$query = "UPDATE ".self::TABLE_NAME.
						" SET uID = ?,
							consumer_key = ?,
							expires = ?
						WHERE " . self::FIELD_KEY . " = ? AND network = ? AND secret = ?";
				$props = array($this->uID,$this->consumer_key,$expiry,$this->token,$this->network,$this->secret);
				$db->Execute($query, $props);
			} else {
				//Attempt to insert the value
				$query = "INSERT INTO ".self::TABLE_NAME."(uID,consumer_key,generated,expires," . self::FIELD_KEY . ",secret,network) VALUES(?,?,?,?,?,?,?)";
				$props = array($this->uID,$this->consumer_key, date(self::DATE_SQLTS),$expiry,$this->token,$this->network,$this->secret);
				$db->Execute($query, $props);
			}
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $this->token;
 	}
}
