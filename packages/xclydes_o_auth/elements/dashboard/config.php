<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Get the config
Loader::model('x_config', XOAUTH_PKGHANDLE);
$pkgConfig = new XConfig();
$pkg = Package::getByHandle(XOAUTH_PKGHANDLE);
$pkgConfig->setPackageObject($pkg);

$coreDashHelper = Loader::helper('concrete/dashboard');
$coreUIHelper = Loader::helper('concrete/interface');
$formHelper = Loader::helper('form');
$pkg = Package::getByHandle(XOAUTH_PKGHANDLE);
$textHelper = Loader::helper('text');
//Load the HTML helper
/* @var $htmlHelper HtmlHelper */
$htmlHelper = Loader::helper('html');
//Add the bootstrap tab js
$this->controller->addHeaderItem( $htmlHelper->javascript("bootstrap-tab.js", XOAUTH_PKGHANDLE) );
$this->controller->addHeaderItem( $htmlHelper->javascript("bootstrap-dropdown.js", XOAUTH_PKGHANDLE) );

$defArgs = array(
		'formHelper' => $formHelper,
		'htmlHelper' => $htmlHelper,
		'imgHelper' => Loader::helper('image'),
		'textHelper' => $textHelper,
		'dtHelper' => Loader::helper('form/date_time'),
		'coreUIHelper' => $coreUIHelper,
		'coreDashHelper' => $coreDashHelper,
		'assetHelper' => Loader::helper('concrete/asset_library'),
		'pkgHandle' => XOAUTH_PKGHANDLE,
		'config' => $pkgConfig,
);


print $coreDashHelper->getDashboardPaneHeaderWrapper(
		t('Configuration'),
		t('Various configuration values used throughout the website.'),
		'span14 offset1',
		false);
?>
<script type="text/javascript">
	$(document).ready(function() {
		
		$('#xoauth-config-tabs a').click(function (e) {
		  e.preventDefault();
		  $(this).tab('show');
		});
		
	});
</script>
<form method="post" action="<?php echo $this->action('config'); ?>"
	method="post" enctype="multipart/form-data">

	<div class="ccm-pane-body tabbable tabs-left">

		<ul id="xoauth-config-tabs" class="tabs nav nav-tabs">
			<li class="active"><a href="#xoauth-users" data-toggle="tab"><?php echo t('Users'); ?>
			</a></li>
			<li><a href="#xoauth-provider" data-toggle="tab"><?php echo t('As Provider'); ?>
			</a></li>
			<li><a href="#xoauth-consumer" data-toggle="tab"><?php echo t('As Consumer'); ?>
			</a></li>
			<li class="dropdown" id="menu1"><a class="dropdown-toggle"
				data-toggle="dropdown" href="#menu1"><?php echo t('Networks')?><b
					class="caret"></b> </a>
				<ul class="dropdown-menu">
					<li><a href="#xoauth-facebook" data-toggle="tab"><?php echo t('Facebook'); ?>
					</a></li>
					<li><a href="#xoauth-twitter" data-toggle="tab"><?php echo t('Twitter'); ?>
					</a></li>
					<li><a href="#xoauth-live" data-toggle="tab"><?php echo t('Live'); ?>
					</a></li>
				</ul>
			</li>
		</ul>

		<div class="tab-content">
			<?php
			//Load the nested elements
			Loader::packageElement("dashboard/config/twitter", XOAUTH_PKGHANDLE, $defArgs);
			Loader::packageElement("dashboard/config/facebook", XOAUTH_PKGHANDLE, $defArgs);
			Loader::packageElement("dashboard/config/live", XOAUTH_PKGHANDLE, $defArgs);
			Loader::packageElement("dashboard/config/users", XOAUTH_PKGHANDLE, $defArgs);
			Loader::packageElement("dashboard/config/consumer", XOAUTH_PKGHANDLE, $defArgs);
			Loader::packageElement("dashboard/config/provider", XOAUTH_PKGHANDLE, $defArgs);
			?>
		</div>
		<br clear="all" />

	</div>
	<div class="ccm-pane-footer">
		<?php echo $coreUIHelper->button(t('Back'), $this->action(''), 'left'); ?>
		<?php echo $coreUIHelper->submit(t('Save'), $this->action("{$this->controller->getTask()}"), 'right', 'primary'); ?>
	</div>

</form>


<?php
print $coreDashHelper->getDashboardPaneFooterWrapper(false);
