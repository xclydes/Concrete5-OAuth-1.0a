<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $config Config */
/* @var $formHelper FormHelper */

?>
<div id="xoauth-consumer" class="tab-pane">

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.consumer.destination.callback]", t('Callback URL')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.consumer.destination.callback]", $config->get('xoauth.consumer.destination.callback')); ?>
			<br /> <span class="help-block"><?php echo t('The URL to which providers are to redirect.');?>
			</span> <span class="help-block"><strong><?php echo t('Default: <em>/xoauth/consumer/verify</em>'); ?></strong></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.consumer.destination.success]", t('On Success')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.consumer.destination.success]", $config->get('xoauth.consumer.destination.success')); ?>
			<br /> <span class="help-block"><?php echo t('The URL to display after a <u>successful</u> auth cycle.');?>
			</span> <span class="help-block"><strong><?php echo t('Default: <em>/profile/xoauth</em>'); ?></strong></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.consumer.destination.failure]", t('On Failure')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.consumer.destination.failure]", $config->get('xoauth.consumer.destination.failure')); ?>
			<br /> <span class="help-block"><?php echo t('The URL to display after a <u>failed</u> auth cycle.');?>
			</span> <span class="help-block"><strong><?php echo t('Default: <em>/login</em>'); ?></strong></span>
		</div>
	</div>

</div>
