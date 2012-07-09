<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $config Config */
/* @var $coreUIHelper ConcreteInterfaceHelper */

?>
<div id="xoauth-facebook" class="tab-pane">

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.facebook.consumer.key]", t('App ID/API Key')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.facebook.consumer.key]", $config->get('xoauth.facebook.consumer.key')); ?><br /> 
			<span class="help-block"><?php echo t('The consumer key assigned to your application by Facebook.');?></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.facebook.consumer.secret]", t('App Secret')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.facebook.consumer.secret]", $config->get('xoauth.facebook.consumer.secret')); ?><br />
			<span class="help-block"><?php echo t('The consumer secret assigned to your application by Facebook.');?></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.facebook.consumer.scope]", t('Scope')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.facebook.consumer.scope]", $config->get('xoauth.facebook.consumer.scope')); ?><br />
			<span class="help-block"><?php echo t('A comma separated list of the permissions being requested.');?></span>
		</div>
	</div>
	
	<div class="input">
		<?php echo $coreUIHelper->button(t('Create An Application'), 'https://developers.facebook.com/apps', 'left', null, array('target'=>"_blank")); ?>
	</div>
		
</div>
