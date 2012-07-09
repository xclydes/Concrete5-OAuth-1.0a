<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));

/**
 * @author jermaine
 *
 */
class XOAuthConnectBlockController extends BlockController {

	var $pobj;

	protected $btDescription = "Provides the ability to display links to the OAuth providers configured.";
	protected $btName = "XOAuth Connect";
	protected $btTable = 'btxoauthconnect';
	protected $btInterfaceWidth = "420";
	protected $btInterfaceHeight = "300";
	protected $btWrapperClass = 'ccm-ui';

	/* (non-PHPdoc)		* @see BlockController::getBlockTypeDescription()	*/
	public function getBlockTypeDescription() {
		return t($this->btDescription);
	}

	/* (non-PHPdoc)
	 * @see BlockController::getBlockTypeName()
	*/
	public function getBlockTypeName() {
		return t($this->btName);
	}

	/* (non-PHPdoc)
	 * @see Controller::on_start()
	*/
	public function on_start(){
		try{
			//Load the vehicle mode;
			Loader::model('x_config', XOAUTH_PKGHANDLE);
			//Get the config
			$pkgConfig = new XConfig();
			$pkg = Package::getByHandle(XOAUTH_PKGHANDLE);
			$pkgConfig->setPackageObject($pkg);
			$this->set('config', $pkgConfig);
		}catch(Exception $error){
			$this->handleError($error);
		}
		parent::on_start();
	}

	/**
	 * 
	 */
	public function view(){
		try {
			//TODO What happens here?
		}catch(Exception $error){
			$this->handleError($error);
		}
	}

	/* (non-PHPdoc)
	 * @see BlockController::save()
	 */
	public function save(){
		//Set the default values if they are missing
		if( !isset($_POST['showTitle']) ){
			$_POST['showTitle'] = 0;
		}
		parent::save($_POST);
	}

	/**
	 * Creates a log entry from the Exception object which is passed.
	 * @param Exception $error The exception to be handled.
	 */
	public function handleError($error, $namespace = 'driveja_vehicles') {
		Log::addEntry("{$error->getMessage()}\n{$error->getTraceAsString()}", $namespace);
	}

}
