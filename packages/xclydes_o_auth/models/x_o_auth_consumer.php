<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the parent class
Loader::library('o_auth', XOAUTH_PKGHANDLE);
Loader::model('abs_x_o_auth_model', XOAUTH_PKGHANDLE);

class XOAuthConsumer extends AbsXOAuthModel {
	
	const FIELD_KEY = 'consumer_key';
	const TABLE_NAME = 'btxoauthconsumers';
	const CACHE_KEY = 'x_oauth_consumer';
	
	public function __construct() {
		//Set the default user
		$currUser = new User();
		$this->uID = $currUser->getUserID();
	}
	
	protected static function getBaseQuery(){
		return 'SELECT a.* 
		FROM ' . self::TABLE_NAME . ' AS a';
	}

	public static function add($name, $description, $callback = NULL, $uId = NULL, $icon_fID = NULL){
		$consumer = null;
		try{
			//Create a new consumer key
			$consumerKey = sha1($uId . ';' .  mt_rand());
			//Create a new consumer secret
			$consumerSecret = hash('sha256', mt_rand() . time() . $consumerKey);
			//Create the consumer
			$consumer = new self();//$consumerKey, $consumerSecret, $uId, $name, $description, $callback);
			$consumer->consumer_key = $consumerKey;
			$consumer->consumer_secret = $consumerSecret;
			$consumer->icon_fID = $icon_fID;
			$consumer->name = $name;
			$consumer->description = $description;
			$consumer->callback_url = $callback;
			//Save the consumer to the database
			$consumer->save();
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $consumer;
	}
	
	public static function getAll(){
		try{
			//Get the database 
			$db = Loader::db();
			$recordSet = $db->Execute(self::getBaseQuery().' ORDER BY '. self::FIELD_KEY .' ASC');
			//Convert the results to vehicles
			$consumers = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, self::CACHE_KEY);
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		//Return an empty array, if necessary.
		return !is_array($consumers) ? array() : $consumers;
	}

	public static function getByKey($consumerKey, $enabledOnly = true){
		$consumer = null;
		try{
			//Attempt to retrieve the item from the cache
			$consumer = Cache::get(self::CACHE_KEY, $consumerKey);
			//It is not cached, load it.
			if( !is_object($consumer) ){
				//Get the base query
				$query = self::getBaseQuery();
				//Added the conditions
				$query .= ' WHERE ' . ($enabledOnly ? 'a.status > 0 AND ' : '').'a.'.self::FIELD_KEY.' = ?';
				//Execute the query
				$db = Loader::db();
				$recordSet = $db->Execute($query, array($consumerKey));
				//Convert the results to vehicles
				$consumers = self::fromRecordset(get_class(), $recordSet, self::FIELD_KEY, self::CACHE_KEY);
				if( !empty($consumers) ){
					//Select the vehicle
					$consumer = array_pop( $consumers );
				}
			}
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $consumer;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($newName){
		$this->name = $newName;
	}
	
	public function getDescription(){
		return $this->description;
	}
	
	public function setDescription($newDesc){
		$this->description = $newDesc;
	}
	
	public function getOwner(){
		if( $this->ownerObj == null ){
			$this->ownerObj = User::getByUserID($this->uID);
		}
		return $this->ownerObj;
	}

	public function setPropertiesFromArray($recordSet){
		try{
			parent::setPropertiesFromArray($recordSet);
			//Load the detail fields
			$this->key = $this->consumer_key;
			$this->secret = $this->consumer_secret;
		}catch(Exception $error){
			$this->handleError($error);
		}
	}
	
	public function toArray(){
		return array();
	}

	public function save(){
		try{
			//Execute the query
			$db = Loader::db();
			//It is an update
			if( $this->generated ){
				$query = "UPDATE ".self::TABLE_NAME.
						" SET uID = ?,
							icon_fID = ?,
							name = ?,
							description = ?,
							callback_url = ?,
							status = ?,
							generated = ?
						WHERE " . self::FIELD_KEY . " = ? AND consumer_secret = ?";
				$props = array($this->uID,$this->icon_fID,$this->name,$this->description,$this->callback_url,$this->status,$this->generated,$this->consumer_key,$this->consumer_secret);
				$db->Execute($query, $props);
			}else{
				//Attempt to insert the value
				$query = "INSERT INTO ".self::TABLE_NAME."(uID," . self::FIELD_KEY . ",consumer_secret,icon_fID,name,description,callback_url,status,generated) VALUES(?,?,?,?,?,?,?,?,?)";
				$props = array($this->uID,$this->consumer_key,$this->consumer_secret,$this->icon_fID,$this->name,$this->description,$this->callback_url,$this->status,date(self::DATE_SQLTS));
				$db->Execute($query, $props);
			}
			//Remove the item from the cache
			Cache::delete(self::CACHE_KEY, $this->consumer_key);
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $this->consumer_key;
 	}
} 