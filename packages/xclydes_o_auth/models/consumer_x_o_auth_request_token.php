<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::model('abs_x_o_auth_token', XOAUTH_PKGHANDLE);

class ConsumerXOAuthRequestToken extends AbsXOAuthToken {
	
	const FIELD_KEY = 'token';
	const TABLE_NAME = 'btxoauthrequestsremote';
	
	protected static function getBaseQuery(){
		return 'SELECT a.* 
		FROM ' . self::TABLE_NAME . ' AS a';
	}

	public static function getByToken($reqToken, $network){
		$token = null;
		try{
			//Get the base query
			$query = self::getBaseQuery();
			//Added the conditions
			$query .= ' WHERE a.'.self::FIELD_KEY.' = ? AND a.network = ?';
			//Execute the query
			$db = Loader::db();
			$recordSet = $db->Execute($query, array($reqToken, $network));
			//Convert the results to vehicles
			$reqTokens = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, null);
			if( !empty($reqTokens) ){
				//Select the vehicle
				$token = array_pop( $reqTokens );
			}
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $token;
	}

	public function getType(){
		return self::TOKEN_REQUEST;
	}
	
	public function getCallBack(){
		return $this->callback_url;
	}

	public function save(){
		try{
			//Execute the query
			$db = Loader::db();
			//Attempt to insert the value
			$query = "INSERT INTO ".self::TABLE_NAME."(callback_url," . self::FIELD_KEY . ",secret,network,generated) VALUES(?,?,?,?,?)";
			$props = array($this->callback_url,$this->token,$this->secret,$this->network,$this->generated);
			$db->Execute($query, $props);
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $this->token;
 	}
}

