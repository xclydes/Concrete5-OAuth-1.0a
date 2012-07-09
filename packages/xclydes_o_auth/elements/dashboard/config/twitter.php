<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $config Config */
?>
<div id="xoauth-twitter" class="tab-pane">

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.twitter.consumer.key]", t('Consumer Key')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.twitter.consumer.key]", $config->get('xoauth.twitter.consumer.key')); ?><br /> 
			<span class="help-block"><?php echo t('The consumer key assigned to your application by Twitter.');?></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.twitter.consumer.secret]", t('Consumer Secret')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.twitter.consumer.secret]", $config->get('xoauth.twitter.consumer.secret')); ?><br /> 
			<span class="help-block"><?php echo t('The consumer secret assigned to your application by Twitter.');?></span>
		</div>
	</div>

	<div class="input">
		<?php echo $coreUIHelper->button(t('Create An Application'), 'https://dev.twitter.com/apps', 'left', null, array('target'=>"_blank")); ?>
	</div>
	
</div>
