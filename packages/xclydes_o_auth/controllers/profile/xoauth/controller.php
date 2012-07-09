<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('x_config', XOAUTH_PKGHANDLE);

class ProfileXoauthController extends Controller {
	
	public function on_start(){
		parent::on_start();
		$this->addHeaderItem(Loader::helper('html')->css('ccm.profile.css'));
		$user = new User();
		$uInfo = UserInfo::getByID($user->uID);
		$this->set('profile', $uInfo);
		$this->set('pkgHandle',  $this->getCollectionObject()->getPackageHandle());
		//Get the application config
		$pkgConfig = new XConfig();
		$pkg = Package::getByHandle( $this->getvar('pkhHandle') );
		$pkgConfig->setPackageObject( $pkg );
		$this->config = $pkgConfig;
	}
	
	public function view(){
		//Display login options/tokens
	}	
}
