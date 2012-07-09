<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

class XclydesOAuthPackage extends Package {

	protected $pkgHandle = 'xclydes_o_auth';
	protected $appVersionRequired = '5.1.0';
	protected $pkgVersion = '1.0.1';

	public function __construct(){
		if( !defined('XOAUTH_PKGHANDLE') ) {
			define('XOAUTH_PKGHANDLE', $this->pkgHandle);
		}
	}

	public static function handleError($error) {
		if( $error instanceof Exception ) {
			//Log::addEntry($error->getTraceAsString(), 'xclydes_o_auth');
			Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}", 'xclydes_o_auth');
		}
	}

	/**
	 * Hook into the start of request processing.
	 */
	public function on_start() {
		//Listen for user deleted events
		Events::extend('on_user_delete', get_class(), 'userDeleted', "./packages/{$this->pkgHandle}/controller.php");
	}

	/**
	 * Listens for deletion activities within the system.
	 * @param Object $userInfo The user which is being deleted.
	 * @return boolean Whether or not to allow the deletion.
	 */
	public function userDeleted($userInfo) {
		$allowDelete = false;
		try {
			if( is_object($userInfo) 
				&& $userInfo->uID ) {
				//Load the AccesToken class
				Loader::model('x_o_auth_access_token', XOAUTH_PKGHANDLE);
				//Get all tokens related to the user
				$userTokens = XOAuthAccessToken::getForUser('%', $userInfo->uID);
				foreach ($userTokens AS $userToken) {
					//Delete each token
					$userToken->delete();
				}
				//Allow the deletion.
				$allowDelete = true;				
			}
		} catch (Exception $error) {
			self::handleError($error);
		}
		return $allowDelete;
	}

	/* (non-PHPdoc)
	 * @see Package::getPackageDescription()
	*/
	public function getPackageDescription() {
		return t('A set of tools which allow users to connect to platforms which support the OAuth protocol.');
	}

	/* (non-PHPdoc)
	 * @see Package::getPackageName()
	*/
	public function getPackageName() {
		return t('Xclydes OAuth');
	}

	/* (non-PHPdoc)
	 * @see Package::install()
	*/
	public function install() {
		$pkg = parent::install();
		if( $pkg ){
			//Call on upgrade
			$pkg->upgrade();
		}
		//Clear the cache
		Cache::flush();
	}

	/* (non-PHPdoc)
	 * @see Package::upgrade()
	*/
	public function upgrade() {
		parent::upgrade();
			
		try {
			$defaultProfilePath = '/profile/xoauth';
			//Setup the default config options
			$pkgConfig = new Config();
			//Focus on the package config.
			$pkgConfig->setPackageObject( $this );
			//Define the default values
			$defConf = array(
					'xoauth.twitter.consumer.key' => '',
					'xoauth.twitter.consumer.secret' => '',
					'xoauth.winlive.consumer.key' => '',
					'xoauth.winlive.consumer.secret' => '',
					'xoauth.winlive.consumer.scope' => 'wl.signin',
					'xoauth.facebook.consumer.key' => '',
					'xoauth.facebook.consumer.secret' => '',
					'xoauth.facebook.consumer.scope' => '',
					'xoauth.consumer.destination.callback' => '/xoauth/consumer/verify',
					'xoauth.consumer.destination.success' => $defaultProfilePath,
					'xoauth.consumer.destination.failure' => '/login',
					'xoauth.provider.verifier.size' => 6,
					'xoauth.provider.icon.default' => '',
					'xoauth.user.new.auto.login' => true,
					'xoauth.user.new.auto.create' => true,
					'xoauth.user.new.format' => '%3$s_%2$s',//%1$s - Screen Name, %2$s - Network UserID, %3$s - Network
					'xoauth.user.new.group' => '',
			);
			//Create any missing values.
			foreach($defConf as $cKey=>$cValue){
				$val = $pkgConfig->get($cKey);
				//If the value does not exist.
				if( !$val ){
					//Create it.
					$pkgConfig->save($cKey, $cValue);
				}
			}
		} catch (Exception $error) {
			self::handleError($error);
		}

		//Define the pages to be created.
		$pkgPages = array(
				"/dashboard/users/xoauth" => array(
						'properties' => array('cName'=>t('Open Authentication'), 'cDescription'=>t('Manages this site\'s OAuth settings.'))
				),
				$defaultProfilePath => array(
						'properties' => array('cName'=>t('Networking'), 'cDescription'=>t('Social networks linked to your account.'))
				),
				"/xoauth" => array(
						'attributes' => array('exclude_nav'=>true),
						'properties' => array('cName'=>t('OAuth Endpoints'), 'cDescription'=>t('General OAuth endpoints.'))
				),
				"/xoauth/provider" => array(
						'attributes' => array('exclude_nav'=>true),
						'properties' => array('cName'=>t('OAuth Provider'), 'cDescription'=>t('Used when the site is an OAuth provider.'))
				),
				"/xoauth/consumer" => array(
						'attributes' => array('exclude_nav'=>true),
						'properties' => array('cName'=>t('OAuth Consumer'), 'cDescription'=>t('Used when the site is an OAuth consumer.'))
				),
		);
		//Add each page defined.
		foreach ($pkgPages as $pkgPageSlug=>$pkgPageSettings) {
			try {
				//Attempt to get the page with the page specified.
				$pkgPage = Page::getByPath($pkgPageSlug);
				if (!is_object($pkgPage) || $pkgPage->isError()) {
					//Create the new page
					$pkgPage = SinglePage::add($pkgPageSlug, $this);
					//If properties are defined
					if( isset( $pkgPageSettings['properties'] ) ) {
						//Update its properties
						$pkgPage->update( $pkgPageSettings['properties'] );
					}
					//If attributes are defined
					if( isset( $pkgPageSettings['attributes'] ) ) {
						//Get the attributes specified.
						$pageAttrs = $pkgPageSettings['attributes'];
						//If it is a non-empty array.
						if( is_array($pageAttrs)
								&& !empty($pageAttrs) ) {
							//Set each attribute.
							foreach ($pageAttrs AS $attrKey=>$attrVal) {
								$pkgPage->setAttribute($attrKey, $attrVal);
							}
						}
					}
				}
			} catch (Exception $error) {
				self::handleError($error);
			}
		}
		// Install block(s)
		Loader::model('block_types');
		$blocks = array(
				'x_o_auth_connect',
		);
		foreach($blocks as $bHandle){
			try {
				$existingBT = BlockType::getByHandle($bHandle);
				if ( !is_object($existingBT) ) {
					BlockType::installBlockTypeFromPackage($bHandle, $this);
				} else {
					echo 'Block exists ' . $bHandle;
				}
			} catch (Exception $error) {
				self::handleError($error);
			}
		}
	}
}