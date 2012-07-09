<?php 
defined('C5_EXECUTE') or die("Access Denied.");

abstract class AbsXOAuthModel extends Object {
		
	const PACKAGE_HANDLE = XOAUTH_PKGHANDLE;
	
	const DATE_SQLDATE = "Y-m-d";
	const DATE_SQLTS = "Y-m-d G:i:s";
	const DATE_DESCRIPTIVE_LONG = "l M d, Y G:i:s";
	const DATE_DESCRIPTIVE_SHORT = "D M d, Y";
	const DATE_COMMON_SHORT = "F j Y, g:i a";
	const DATE_COMMON_LONG = 'l jS \of F Y h:i:s A';
	const DATE_YEAR = 'Y';
	const DATE_CREATED = 'DATE_CREATED';

	const MEDIA_IMAGE = "img";

	public $status = true;
	
	protected $package;
		
	public abstract function toArray();	
	public abstract function save();	
	
	public function delete() {
		$deleted = false;
		try{
			//Mark as deleted
			$this->status = false;
			//Update the database
			$this->save();
			$deleted = true;
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $deleted;
	}
	
	protected function getPackage(){
		try{
			if( !$this->package ){
				//Load the package
				$this->package = Package::getByHandle(self::PACKAGE_HANDLE);
			}
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $this->package;			
	}
	
	public function formatDate($date, $format = self::DATE_SQLDATE){
		//Set the output to be the original date.
		$output = $date;
		try{
			if( $output != ''
			   	&& $output != NULL){
				//Assume the birthdate is already a timestamp.
				$ts = $output;
				//If the birthdate is not already a timestamp. Make it so.
				if( !is_numeric($output) ){
					//Get the timestamp from the stored birth date.
					$ts = strtotime($output);
				}
				//If the timestamp is numeric, and the format is not blank, re-cast it.
				if( is_numeric($ts)
					&& !empty($format) ){
					//Cast it based on the format specified.
					$output = date( $format, $ts);
				}
			}
		}catch(Exception $error){
			$this->handleError($error);
		}
		return $output;
	}
	
	protected function handleError($error){
		if( is_object($error) ) { 
			//Save the stack trace
			Log::addEntry($error->getMessage().'\n'.$error->getTraceAsString());
		}
	}
	
	protected static function fromRecordset($instanceClass, $recordSet, $fieldKey, $cacheKey = null){
		$models = array();
		try{
			if ( $recordSet ) {
				while ( !$recordSet->EOF ) {
					//Detemine the model's id
					$recordId = $recordSet->fields[$fieldKey];
					//Get the model if it is cached
					$model = $cacheKey ?
						Cache::get($cacheKey, $recordId) :
						null;
					if( !is_object($model) ){
						//Instantiate a new model
						$model = new $instanceClass();
						//Set the properties of the model
						$model->setPropertiesFromArray($recordSet->fields);
						//Cache the model for future reference
						if( $cacheKey ){
							Cache::set($cacheKey, $recordId, $model);
						}
					}
					if( is_object($model) ){
						//Add the document to the listing
						//array_push($models, $model);
						$models[$recordId] = $model;
					}
					$recordSet->MoveNext();
				}
				//Close the recordset to free resources
				$recordSet->Close();
			}			
		}catch(Exception $error){
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
		}
		return $models;
	}

}