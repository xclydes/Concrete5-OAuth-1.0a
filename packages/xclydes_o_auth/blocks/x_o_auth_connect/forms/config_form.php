<?php  
defined('C5_EXECUTE') or die(_("Access Denied."));
/* @var $formHelper FormHelper */

$sizeOptions = array(
		's' => t('Small'),
		'm' => t('Medium'),
		'l' => t('Large'),
);

$shadeOptions = array(
		'l' => t('Light'),
		'd' => t('Dark'),
);

try {
	//Assume block layout
	if( !$iconLayout ) {
		$iconLayout = 'block';
	}
	$formHelper = Loader::helper('form');
	?>
<div class="ccm-ui">

	<div class="clearfix ">
		<?php echo $formHelper->label('defTitle', t('Title')); ?>
		<div class="input">
			<?php echo $formHelper->hidden("showTitle", true); ?>
			<?php echo $formHelper->text('defTitle', $defTitle); ?>
			<br clear="all" /> <span class="help-block">The title to display, if
				so desired.</span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("iconSize", t('Icon Size')); ?>
		<div class="input">
			<?php echo $formHelper->select("iconSize", $sizeOptions, $iconSize); ?>
			<br /> <span class="help-block"><?php echo t('The size of network icons to be displayed.');?>
			</span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("iconShade", t('Icon Shade')); ?>
		<div class="input">
			<?php echo $formHelper->select("iconShade", $shadeOptions, $iconShade); ?>
			<br /> <span class="help-block"><?php echo t('The shade of network icons to be displayed.');?>
			</span>
		</div>
	</div>

	<div class="clearfix">
		<?php echo $formHelper->label("iconLayout", t('Existing Networks')); ?>
		<div class="input">
			<?php echo $formHelper->radio("iconLayout", 'block', $iconLayout); ?> &nbsp; <?php echo t('List'); ?><br />
			<?php echo $formHelper->radio("iconLayout", 'inline', $iconLayout); ?> &nbsp; <?php echo t('Inline'); ?>
			<span class="help-block"><?php echo t('The layout to applied to the available network icons.');?>
			</span>
		</div>
	</div>
	
	<div class="clearfix">
		<?php echo $formHelper->label("handleExisting", t('Existing Networks')); ?>
		<div class="input">
			<?php echo $formHelper->radio("handleExisting", 0, $handleExisting); ?> &nbsp; <?php echo t('Hidden'); ?><br />
			<?php echo $formHelper->radio("handleExisting", 1, $handleExisting); ?> &nbsp; <?php echo t('Disabled'); ?><br />
			<?php echo $formHelper->radio("handleExisting", 2, $handleExisting); ?> &nbsp; <?php echo t('Link To Profile'); ?><br />
			<?php echo $formHelper->radio("handleExisting", 3, $handleExisting); ?> &nbsp; <?php echo t('Show Profile'); ?>
			<br /> <span class="help-block"><?php echo t('How to display networks already associated.');?>
			</span>
		</div>
	</div>

</div>

<?php
} catch (Exception $error) {
	$controller->handleError($error, 'xoauth_connect - config.php');
}