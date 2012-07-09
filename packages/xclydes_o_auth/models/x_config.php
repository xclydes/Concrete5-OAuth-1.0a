<?php
defined('C5_EXECUTE') or die("Access Denied.");

class XConfig extends Config {

	public function get($cfKey, $getFullObject = false, $defVal = null) {
		//Get the value from the parent first
		$currVal = parent::get($cfKey, $getFullObject);
		//If no value was found, and default value is set.
		if( $defVal != null
			&& $currVal == null ) {
			//Use the default value
			$currVal = $defVal;
		}
		return $currVal;
	}
	
}