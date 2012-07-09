<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('x_config', XOAUTH_PKGHANDLE);

class DashboardUsersXoauthController extends Controller {
	
	public function on_start(){
		//Get the package config.
		$pkgConfig = new XConfig();
		$pkg = Package::getByHandle(XOAUTH_PKGHANDLE);
		$pkgConfig->setPackageObject($pkg);
		$this->set('config', $pkgConfig);
	}

	public function view(){
	}
	
	public function config(){
		if( $this->isPost() ){
			$submittedConf = $this->post('xoauth_config');
			//Get the package config.
			$pkgConfig = $this->getvar('config');
			foreach($submittedConf AS $cfKey=>$cfValue){
				$pkgConfig->save($cfKey, $cfValue);
			}
			$this->set('message', 'The config has been updated.');
		}
	}
	
	public function consumers($consumerKey = ''){
		//Load the consumer class
		Loader::model('x_o_auth_consumer', XOAUTH_PKGHANDLE);
		//Get the consumer specified
		$consumer = XOAuthConsumer::getByKey($consumerKey, false);
		if( $this->isPost() ){
			//Validate the fields posted
			$formValidator = Loader::helper('validation/form');
			$formValidator->setData( $_POST );
			//Name and description are the only required fields
			$formValidator->addRequired('consumer_name', t('A name is required.'));
			$formValidator->addRequired('consumer_description', t('A description is required.'));
			$formValidator->addRequired('callback_url', t('A callback url is required.'));
			if ( !$formValidator->test() ) {
				$errorArray = $formValidator->getError()->getList();
				//Set the error messages
				$this->set('error', $errorArray);
			} else { //Create/update the consumer
				$name = $this->post('consumer_name');
				$description = $this->post('consumer_description');
				$callBack = $this->post('callback_url');
				$icon_fID = $this->post('icon_fID');
				$status = $this->post('status');
				if( is_object($consumer) ){
					//Update the consumer
					$consumer->name = $name;
					$consumer->description = $description;
					$consumer->callback_url = $callBack;
					$consumer->icon_fID = $icon_fID;
					$consumer->status = $status;
					$consumer->save();
					$this->set('message', 'The consumer has been updated.');
				} else {//Add a new consumer
					//Create the new consumer. Status is enabled by default.
					$consumer = XOAuthConsumer::add($name, $description, $callBack, NULL, $icon_fID);
					$this->set('message', 'The consumer has been created.');
				}
			}
		}
		//Set the consumer
		$this->set('consumer', $consumer);
	}

}
