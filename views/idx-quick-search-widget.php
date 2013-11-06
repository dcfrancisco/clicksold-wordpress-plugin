<?php echo $before_widget; ?>
<div id="<?php echo $this->get_field_id("") ?>container" class="<?php echo $this->widget_options['classname'] ?>-container widget cs-widget" style="<?php echo $widget_styles ?>">
  <div class="csFeatureListingModule" class="widgetContent">
    <div class="csFeatureListingWidget">
	  Please Wait...
    </div>
  </div>
</div>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$("#<?php echo $widget_id ?>").IDXQuickSearchWidget({
			ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
			plugin : true,
            idxSearchUrl : "<?php echo $idx_url ?>",
			csInitPropType : <?php echo $default_prop_type ?>
		});
	});
})(csJQ);
</script>
<?php echo $after_widget; ?>