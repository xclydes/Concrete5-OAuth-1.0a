<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

//Ensure the file class is loaded
Loader::model('file');
print $coreDashHelper->getDashboardPaneHeaderWrapper(
	t('Consumer'), 
	t('Manages consumers of this website.'),
	'span16', 
	false);
$consumerKey = $consumer ? $consumer->consumer_key : '';
?>

<form method="post" action="<?php echo $this->action("consumers/{$consumerKey}"); ?>" enctype="multipart/form-data">

    <div class="ccm-pane-body">
    	<div class="row">
        	<div class="span7">
            	<!-- Consumers -->
            <table class="zebra-striped">
                <thead>
                	<tr>
	                	<th>Icon</th>
	                	<th>Name</th>
	                    <th>Edit</th>
                	</tr>
                </thead>
                <tbody>
				<?php
                    //Get the list of consumers
                    $allConsumers = XOAuthConsumer::getAll();
					$imgSize = 64;
                    if( !empty($allConsumers) ){
                        foreach($allConsumers as $conKey=>$exCons) {
						//Get the image file, if any
						$consIconFile = File::getByID($exCons->icon_fID);
							?>
                            <tr>
                            	<td><?php print $consIconFile ? $imgHelper->outputThumbnail($consIconFile, $imgSize, $imgSize) : '';?></td>
                            	<td><?php echo $exCons->name . '<br /><strong>' . ($exCons->status ? 'Enabled' : 'Disabled') . '</strong>';?></td>
                                <td><?php echo $coreUIHelper->button(t('Edit'), $this->action("consumers/{$exCons->consumer_key}"), 'left'); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
						?>
						<tr>
							<td colspan="3"><?php echo t('No Consumers Found!');?></td>
						</tr>
						<?php
                    }
                ?>
                </tbody>
            </table>
                <!-- //Consumers -->
            </div>
        	<div class="span7">
            	<!-- Consumer Form -->
                <div class="clearfix">
                    <?php echo $formHelper->label("consumer_name", t('Name')); ?>
                    <div class="input">
                        <?php echo $formHelper->text("consumer_name", $consumer ? $consumer->name : ''); ?>
                    </div>
                </div>
                <div class="clearfix">
                    <?php echo $formHelper->label("consumer_description", t('Description')); ?>
                    <div class="input">
                        <?php echo $formHelper->textarea("consumer_description", $consumer ? $consumer->description : ''); ?>
                    </div>
                </div>
                 <div class="clearfix">
                    <?php echo $formHelper->label('consumer_key', t('Key')); ?>
                    <div class="input">
                    	<?php echo $formHelper->textarea("key", $consumerKey, array('disabled'=>'DISABLED')); ?>
                    </div>
                </div>
                 <div class="clearfix">
                    <?php echo $formHelper->label('consumer_secret', t('Secret')); ?>
                    <div class="input">
                    	<?php echo $formHelper->textarea("secret", $consumer ? $consumer->consumer_secret : '', array('disabled'=>'DISABLED')); ?>
                    </div>
                </div>
				 <?php 
                    if( is_object($consumer)
						&& $consumer->icon_fID > 0) { 
						//Get the file
						$consImgFile = File::getByID($consumer->icon_fID);
                    }
                ?>
              	<div class="clearfix">
                    <?php echo $formHelper->label('icon_fID', t('Icon')); ?>
                    <div class="input">
                        <?php echo $assetHelper->image('icon', 'icon_fID', t('Select Icon'), $consImgFile); ?>
                    </div>
                </div>
                <div class="clearfix">
                    <?php echo $formHelper->label("callback_url", t('Def. Callback')); ?>
                    <div class="input">
                        <?php echo $formHelper->url("callback_url", $consumer ? $consumer->callback_url : ''); ?>
                    </div>
                </div>
                <div class="clearfix">
                    <?php echo $formHelper->label('generated', t('Enabled') . '/' . t('Created')); ?>
                    <div class="input input-prepend">
                    	<span class="add-on"><?php echo $formHelper->checkbox("status", true, $consumer ? $consumer->status : false); ?></span>
                        <span class="uneditable-input">&nbsp;<?php echo $consumer ? $consumer->formatDate($consumer->generated, XOAuthConsumer::DATE_DESCRIPTIVE_SHORT) : ''; ?></span>                
                    </div>
                </div>
               <!-- //Consumer Form -->
            </div>
        </div>
        <br clear="all" />
        
    </div>
    <div class="ccm-pane-footer">
        <?php echo $coreUIHelper->button(t('Back'), $this->action(''), 'left'); ?>
        <?php echo $coreUIHelper->submit(t('Save'), $this->action("{$this->controller->getTask()}"), 'right', 'primary'); ?>
        <?php echo $coreUIHelper->button(t('New'), $this->action("{$this->controller->getTask()}"), 'right', 'info'); ?>
    </div>
    
</form>

<?php
print $coreDashHelper->getDashboardPaneFooterWrapper(false);
