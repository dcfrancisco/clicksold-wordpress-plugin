<?php echo $before_widget; ?>
<?php
	// 2015-02-09 - a while back this widget was expanded with some extra options. Widgets that were added before this happened will not have
	//              the following values set. We set them up here. We define these such that the widget acts as it did before the changes.
	if( !isset( $showSearchButton ) ) { $showSearchButton = 0; }
	if( !isset( $showMLSSearchLogo ) ) { $showMLSSearchLogo = 1; }
?>


<div id="<?php echo $this->get_field_id("") ?>container" class="<?php echo $this->widget_options['classname'] ?>-container widget cs-widget" <?php echo $widgetStyles; ?>>
  <div class="<?php echo $this->widget_options['classname']; ?>-holder" <?php echo $formContainerStyles; ?>>
<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
    <form id="<?php echo $this->get_field_id("listing_quick_search_form") ?>" class="" method="get">
<?php if( !empty( $showMLSSearchLogo ) && $showMLSSearchLogo == 1 ) { echo '<img src="' . plugins_url('images/se_MLS_logo_91x30.png', dirname(__FILE__) ) . '" style="vertical-align:middle">'; } ?>
<?php echo'<input id="' . $this->get_field_id("search_text") . '" class="s" type="text" name="term" value="MLS&reg;, Community, etc." />'?>
<?php if( !empty( $showSearchButton ) && $showSearchButton == 1 ) { echo '<input type="button" name="Search" value="Search" />'; } ?>



    </form>
  </div>
</div>
<script>
(function($){
	$(document).ready(function(){
		$("#<?php echo $this->get_field_id("") ?>container").ListingQuickSearchWidget({
			ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
			idx_url : "<?php echo $idx_url ?>",
			listings_url : "<?php echo $listings_url ?>",
			comm_url : "<?php echo $comm_url ?>",
			using_permalinks : <?php echo $using_permalinks ?>, 
			searchButton : <?php echo $showSearchButton; ?>
		});
	});
})(csJQ);
</script>
<?php echo $after_widget; ?>
