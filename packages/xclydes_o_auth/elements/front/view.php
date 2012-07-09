<?php  defined('C5_EXECUTE') or die("Access Denied.");
Loader::model('user_attributes');
$pkgConfig = new XConfig();
$package = Package::getByHandle(XOAUTH_PKGHANDLE);
$pkgConfig->setPackageObject($package);

/* @var $htmlHelper HtmlHelper */
/* @var $controller Controller */
/* @var $config XConfig */

$controller = $this->controller;
$controller->addHeaderItem($htmlHelper->css('buttons.css', XOAUTH_PKGHANDLE));

//Get the preferred color and size
$size = $pkgConfig->get('xoauth.icons.display.size', false, 's');
$shade = $pkgConfig->get('xoauth.icons.display.shade', false, 'd');
?>

<div id="ccm-profile-wrapper">
	<?php  Loader::element('profile/sidebar', array('profile'=> $profile)); ?>
	<div id="ccm-profile-body">
		<div id="ccm-profile-body-attributes">
			<div class="ccm-profile-body-item">

				<h1>
					<?php echo $profile->getUserName()?>
				</h1>
				<?php 
				$uaks = UserAttributeKey::getPublicProfileList();
        foreach($uaks as $ua) { ?>
				<div>
					<label><?php echo $ua->getKeyName()?> </label>
					<?php echo $profile->getAttribute($ua, 'displaySanitized', 'display'); ?>
				</div>
				<?php  } ?>

			</div>

		</div>

		<div class="xoauth">
			<?php
			$xOAuthHelper = Loader::helper('x_o_auth_consumer', $pkgHandle);
			Loader::model('x_o_auth_access_token', $pkgHandle);
			//Get the list of remote networks
			$networks = $xOAuthHelper->getRemoteNetworks();
			foreach($networks as $networkType=>$networkName){
				$xOAuthHelper->setAccessToken(null);
				//Get the keys stored for the this user and network
				$accessTokens = XOAuthAccessToken::getForUser($networkType);
				//Adjust the store on the helper
				$xOAuthHelper->setStore( new ConsumerXOAuthStore($networkType) );
				$loginRequired = true;
				if( !empty($accessTokens) ) {
					//Display the keys found
					foreach($accessTokens as $tokenKey=>$accToken){
						//Set the access token
						$xOAuthHelper->setAccessToken($accToken);
						//Get the user profile
						$userProfile = $xOAuthHelper->getProfile();
						if( is_object($userProfile)
								&& $userProfile->valid ){
							echo '<h3 class="stylish">'.$networkName.'</h3>';
							?>
			<div>
				<div class="photo apply-corners apply-shadows-oval float-left">
					<img src="<?php echo $userProfile->image;?>" class="float-left" />
				</div>
				<div class="float-left">
					<span><?php echo $userProfile->name;?> </span><br /> <a
						class="button button-grey stylish"
						href="<?php echo $userProfile->profile;?>" target="_blank">Profile</a>
				</div>
			</div>
			<br clear="all" />
			<?php
			$loginRequired = false;
						}
					}
				}
				if( $loginRequired ) {
					$networkUrl =  $xOAuthHelper->getUrl();
					if( $networkUrl ) {
						//echo 'URL: ' . $networkUrl;
						echo "<a href=\"{$networkUrl}\" class=\"{$networkType}-{$shade}-{$size}\"></a>";
					}
				}
			}
			?>
		</div>

		<?php  
		$a = new Area('Main');
		$a->setAttribute('profile', $profile);
		$a->setBlockWrapperStart('<div class="ccm-profile-body-item">');
		$a->setBlockWrapperEnd('</div>');
		$a->display($c);
		?>

	</div>

	<div class="ccm-spacer"></div>

</div>
