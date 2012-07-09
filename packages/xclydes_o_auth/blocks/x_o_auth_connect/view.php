<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));

$pkgHandle = XOAUTH_PKGHANDLE;

?>

<?php if( $defTitle ) { ?>
	<h3><?php echo $defTitle; ?></h3>		
<?php } ?>
<div class="xoauth">
	<?php
	/* @var $xOAuthHelper XOAuthConsumerHelper */
	//Load the OAuth consumer helper
	$xOAuthHelper = Loader::helper('x_o_auth_consumer', $pkgHandle);
	
	//Load the access token model
	Loader::model('x_o_auth_access_token', $pkgHandle);
	
	//Get the list of remote networks
	$networks = $xOAuthHelper->getRemoteNetworks();
	
	//Process the list of networks
	foreach($networks as $networkType=>$networkName){
		//Clear the helper token
		$xOAuthHelper->setAccessToken(null);
		
		//Get the keys stored for the this user and network
		$accessTokens = XOAuthAccessToken::getForUser($networkType);
		
		//Adjust the store on the helper
		$xOAuthHelper->setStore( new ConsumerXOAuthStore($networkType) );
		
		//Assume the user is not associated with the network
		$loginRequired = true;
		//If a token was found
		if( !empty($accessTokens) ) {
			//Display the keys found
			foreach($accessTokens as $tokenKey=>$accToken){
				//Set the access token
				$xOAuthHelper->setAccessToken($accToken);
				//Get the user profile
				$userProfile = $xOAuthHelper->getProfile();
				//If a valid profile was found
				if( is_object($userProfile)
						&& $userProfile->valid ){
					//Indicate that no login is required.
					$loginRequired = false;
					//Display the user's details based on the settings
					switch( $handleExisting ) {
						case 3:
							//Show the user's profile.
						?>
							<h3><?php echo $networkName; ?></h3>
							<div>
								<span class="photo apply-corners apply-shadows-oval float-left">
									<img src="<?php echo $userProfile->image;?>" class="float-left" />
								</span>
								<span class="float-left">
									<a class="button stylish" href="<?php echo $userProfile->profile;?>" target="_blank"><?php echo $userProfile->name;?></a>
								</span>
							</div>
						<?php 
							break;
						case 2:
							//Show a link to the user's profile.
						?>
							<a class="button stylish" href="<?php echo $userProfile->profile;?>" target="_blank"><?php echo $networkName; ?></a>
						<?php 
							break;
						case 1:
							//Display the link, but disabled.
						?>
							<a class="<?php echo "{$networkType}-{$iconShade}-{$iconSize}"; ?>">&nbsp;</a>
						<?php 
							break;
						case 0:
						default:
							//Show nothing
							break;
					}
				}
			}
		}
		if( $loginRequired ) {
			$networkUrl =  $xOAuthHelper->getUrl();
			if( $networkUrl ) {
				//echo 'URL: ' . $networkUrl;
				echo "<a href=\"{$networkUrl}\" class=\"{$networkType}-{$iconShade}-{$iconSize}\"></a>";
			}
		}
	}
	?>
</div>
