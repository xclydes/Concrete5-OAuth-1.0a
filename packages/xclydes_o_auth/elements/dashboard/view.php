<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $coreDashHelper ConcreteDashboardHelper */

$package = Package::getByHandle(XOAUTH_PKGHANDLE);

print $coreDashHelper->getDashboardPaneHeaderWrapper(
	t('Summary'), 
	t('A brief summary of various customers and vehicles within the system.'),
	null, 
	true, 
	array(),
	false);
?>
<!-- Button Style -->

<link rel="stylesheet" media="screen,projection" type="text/css" href="<?php echo $package->getRelativePath(); ?>/elements/css/buttons.css" />
<!-- //Button Style -->
    <div class="ccm-pane-options well">
        <?php 
			echo $coreUIHelper->button(t('Consumers'), $this->action('consumers'), 'left') . '&nbsp;';
			echo $coreUIHelper->button(t('Settings'), $this->action('config'), 'left'); 
		?>
    </div>
	<br class="clearfix" />
	<p>OAuth statistics comming soon!</p>
    <div class="xoauth row">
    	<div class="span4">
        </div>
        <div class="span4">
			<?php
                //Get the tokens for the current user
                $xOAuthHelper = Loader::helper('x_o_auth_consumer', XOAUTH_PKGHANDLE);
                Loader::model('x_o_auth_access_token', XOAUTH_PKGHANDLE);
                //Get the list of remote networks
                $networks = $xOAuthHelper->getRemoteNetworks();
               /* foreach($networks as $networkType=>$networkName){
                    //Get the keys stored for the this user and network
                    $accessTokens = XOAuthAccessToken::getForUser($networkType);
					print_r($accessTokens);
                    if( !empty($accessTokens) ) {
                        //Display the keys found
                        foreach($accessTokens as $tokenKey=>$accToken){
                            echo $tokenKey . '<br />';
                        }
                    } else {
                        //Adjust the store on the helper
                        $xOAuthHelper->setStore( new ConsumerXOAuthStore($networkType) );
                        echo "<a href=\"{$xOAuthHelper->getUrl()}\" class=\"{$networkType}-dark-small\" target=\"_blank\"></a>";//$coreUIHelper->button($networkName, , 'left', strtolower($networkType).'-dark-large', array('target'=>'_blank')) . '<br />';
                    }
                }*/
            ?>
        </div>
     </div>

<?php
print $coreDashHelper->getDashboardPaneFooterWrapper();
