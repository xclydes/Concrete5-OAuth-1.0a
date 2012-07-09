<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $config Config */
/* @var $formHelper FormHelper */

//Get the current user group
$usrGroup = Group::getByID($config->get('xoauth.user.new.group'));
if($usrGroup instanceof Group) {
	$grpName = $usrGroup->getGroupName();
	$grpID = $usrGroup->getGroupID();
}

?>

<script type="text/javascript">
$(function() {
	$('#groupSelector').dialog();
	ccm_triggerSelectGroup = function(gID, gName) {
		$('#xoauth_config\\[xoauth\\.user\\.new\\.group\\]').val(gID);
		$('#groupName').text(gName);
	};
});
</script>

<div id="xoauth-users" class="tab-pane active">

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.user.new.auto.login]", t('Auto. Login')); ?>
		<div class="input">
			<?php echo $formHelper->checkbox("xoauth_config[xoauth.user.new.auto.login]", 1, $config->get('xoauth.user.new.auto.login')); ?><br />
			<span class="help-block"><?php echo t('Automatically login as newly authenitcated users.');?></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.user.new.auto.create]", t('Auto. Create')); ?>
		<div class="input">
			<?php echo $formHelper->checkbox("xoauth_config[xoauth.user.new.auto.create]", 1, $config->get('xoauth.user.new.auto.create')); ?><br /> 
			<span class="help-block"><?php echo t('Automatically create newly authenticated users.');?></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.user.new.format]", t('Username Format')); ?>
		<div class="input">
			<?php echo $formHelper->text("xoauth_config[xoauth.user.new.format]", $config->get('xoauth.user.new.format')); ?><br />
			<span class="help-block"><?php echo t('The format to use when generating new usernames. %1$s - Screen Name, %2$s - Network UserID, %3$s - Network');?></span>
			<span class="help-block"><strong><?php echo t('Default: <em>%3$s_%2$s</em>'); ?></strong></span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("xoauth_config[xoauth.user.new.group]", t('User Group')); ?>
		<div class="input">
			<?php echo $formHelper->hidden("xoauth_config[xoauth.user.new.group]", $grpID); ?>
			<div class="ccm-summary-selected-item">
				<strong><span id="groupName"><?php  echo $grpName ?></span></strong>
				<div><a id="groupSelector" href="<?php  echo REL_DIR_FILES_TOOLS_REQUIRED?>/user_group_selector.php?mode=groups"><?php  echo t('Select Group')?></a></div>
			</div>
			<span class="help-block"><?php echo t('The group to which newly created users should be added.');?></span>
		</div>
	</div>
	
</div>
