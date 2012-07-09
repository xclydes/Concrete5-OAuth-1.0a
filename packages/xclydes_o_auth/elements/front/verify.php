<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

if( is_object($accessTokenObj) 
	&& $accessTokenObj->success ){
?>
	<p>Congratulation</p>
    <p>
	<?php 
		echo 'Access: ' . print_r($accessTokenObj, true) . '<br />';
		echo 'User: ' . print_r(new User(), true) . '<br />';
		echo 'User: ' . print_r($_SESSION, true) . '<br />';
	?>
    </p>

<?php
} else {
}
