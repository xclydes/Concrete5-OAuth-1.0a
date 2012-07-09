<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Load the oauth store
$pkgHandle = 'xclydes_o_auth';
Loader::model('x_config', $pkgHandle);
Loader::model('provider_x_o_auth_store', $pkgHandle);


class XOAuthProviderHelper {

	const NETWORK_LOCAL = 'local';

	protected static $_pkgHandle= 'xclydes_o_auth';
	
	protected $config;
	protected $oauthStore;
	protected $oauthServer;
	
	public function __construct(){
		//Get the config
		$pkgConfig = new XConfig();
		$pkg = Package::getByHandle(self::$_pkgHandle);
		$pkgConfig->setPackageObject($pkg);
		$this->config = $pkgConfig;
		//Clean up the nonce and request tables.
		ProviderXOAuthStore::cleanup();
		$this->oauthStore = new ProviderXOAuthStore();
		$this->oauthServer = new OAuthServer( $this->oauthStore );
		$this->oauthServer->add_signature_method( new OAuthSignatureMethod_HMAC_SHA1() );
		$this->oauthServer->add_signature_method( new OAuthSignatureMethod_PLAINTEXT() );
	}
	
	public function currentRequest(){
		return OAuthRequest::from_request();
	}
	
	protected function handleError($error){
		if( is_object($error) ) { 
			//Save the stack trace
			Log::addEntry($error->getMessage().'\n'.$error->getTraceAsString());
		}
	}
}
