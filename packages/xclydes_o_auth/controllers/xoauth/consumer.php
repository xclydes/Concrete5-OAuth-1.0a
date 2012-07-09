<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::model('x_config', XOAUTH_PKGHANDLE);

class XoauthConsumerController extends Controller {
	
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
	
	public function verify($provider = 'local', $redirectToken = ''){
		//Load the auth helper
		$xOAuthHelper = Loader::helper('x_o_auth_consumer', $this->getvar('pkgHandle') );
		$message = '';
		$provider = strtolower($provider);
		//Process the redirect token, if any.
		$onSuccess = '';
		$verifier = null;
		$oauth_token = null;
		$xOAuthHelper->setStore( new ConsumerXOAuthStore($provider) );
		switch( $provider ){
			case XOAuthConsumerHelper::NETWORK_WINDOWSLIVE:
				$verifier = $this->get('code');
				break;
			case XOAuthConsumerHelper::NETWORK_TWITTER:
				$verifier = $this->get('oauth_verifier');
				$oauth_token = $this->get('oauth_token');
				break;
			case XOAuthConsumerHelper::NETWORK_FACEBOOK:
				$verifier = $this->get('state');//Verification
				$oauth_token = $this->get('code');//Secret
				break;
			default://Login locally
				//This page is not the for that purpose.
				//Redirect to the home page.
				$this->set('error', 'Read as local.');
				break;
		}
		/* @var $xOAuthHelper XOAuthConsumerHelper */
		$accToken = $xOAuthHelper->getAccessToken($verifier, $oauth_token);
		//Redirect to the appropriate page.
		$redirectTo = $this->config->get('xoauth.consumer.destination.failure', false, '/login');
		if( is_object($accToken) 
			|| $accToken->success ){
			//Redirect to the success
			$redirectTo = $this->config->get('xoauth.consumer.destination.success', false, '/profile/xoauth');
		}
		$this->redirect($redirectTo);
	}
}
