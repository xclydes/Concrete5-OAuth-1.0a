<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

try{
	$task = $this->controller->getTask();
	$defArgs = array(
		'formHelper' => Loader::helper('form'), 
		'htmlHelper' => Loader::helper('html'), 
		'textHelper' => Loader::helper('text'),
		'dtHelper' => Loader::helper('form/date_time'), 
		'coreUIHelper' => Loader::helper('concrete/interface'),
		'coreDashHelper' => Loader::helper('concrete/dashboard'),
		'assetHelper' => Loader::helper('concrete/asset_library'),
		'accessTokenObj'=>$accessTokenObj,
		'profile' => $profile,
	);
	//Determine the template to be loaded.
	$templateName = "front/{$task}";
	//Load the template
	Loader::packageElement($templateName, XOAUTH_PKGHANDLE, $defArgs);
} catch(Exception $error) {
	Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}");
}


