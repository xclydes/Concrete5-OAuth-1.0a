<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $config Config */
//Get the default image defined.
$defImgFile = File::getByID($config->get('xoauth.provider.icon.default'));

?>
<div id="xoauth-provider" class="tab-pane">

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.provider.verifier.size]", t('Verifier Size')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.provider.verifier.size]", $config->get('xoauth.provider.verifier.size')); ?>
			<br /> <span class="help-block"><?php echo t('The number of characters to be used when generating an request verifier.');?>
			</span> <span class="help-block"><strong><?php echo t('Default: <em>6</em>'); ?>
			</strong> </span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label('xoauth_config[xoauth.provider.icon.default]', t('Default Icon')); ?>
		<div class="input">
			<?php echo $assetHelper->image('default_icon', 'xoauth_config[xoauth.provider.icon.default]', t('Select Icon'), $defImgFile); ?>
			<span class="help-block"><?php echo t('The icon to be displayed for a consumer if one is not set.');?>
			</span>
		</div>
	</div>

</div>
