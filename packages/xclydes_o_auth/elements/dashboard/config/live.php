<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $config Config */
?>
<div id="xoauth-live" class="tab-pane">

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.winlive.consumer.key]", t('Client ID')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.winlive.consumer.key]", $config->get('xoauth.winlive.consumer.key')); ?>
			<br /> <span class="help-block"><?php echo t('The consumer key assigned to your application by Live.');?>
			</span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.winlive.consumer.secret]", t('Client secret')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.winlive.consumer.secret]", $config->get('xoauth.winlive.consumer.secret')); ?>
			<br /> <span class="help-block"><?php echo t('The consumer secret assigned to your application by Live.');?>
			</span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.winlive.consumer.scope]", t('Scope')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.winlive.consumer.scope]", $config->get('xoauth.winlive.consumer.scope')); ?><br />
			<span class="help-block"><?php echo t('A space separated list of the permissions being requested.');?></span>
			<span class="help-block"><strong><?php echo t('Default: <em>wl.signin</em>'); ?>
			</strong> </span>
		</div>
	</div>
	
	<div class="input">
		<?php echo $coreUIHelper->button(t('Create An Application'), 'https://manage.dev.live.com/Applications/Index', 'left', null, array('target'=>"_blank")); ?>
	</div>

</div>
