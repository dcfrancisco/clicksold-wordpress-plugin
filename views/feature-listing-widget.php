<?php 
echo $before_widget; 
if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
?>
<div id="<?php echo $this->get_field_id("") ?>container" class="<?php echo $this->widget_options['classname'] ?>-container widget">
  <div class="csFeatureListingModule" class="widgetContent">
    <div class="csFeatureListingWidget">
	  Please Wait...
    </div>
  </div>
</div>
<script>
(function($){
	$(document).ready(function(){
		var w_width = $("#<?php echo $this->get_field_id("") ?>container").children(".csFeatureListingModule").width();
		$("#<?php echo $this->get_field_id("") ?>container").children(".csFeatureListingModule").css("min-height", (w_width * 1.036) + "px");
	
		$("#<?php echo $this->get_field_id("") ?>container").FeatureListingWidget({
			plugin : true,
			ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
<?php if($this->BROKERAGE === false) { ?>
			listingSection : <?php echo $instance['listing_section']; ?>,
<?php } ?>			
			listingType : <?php echo $instance['listing_type']; ?>,
			listingStatus : "<?php echo $instance['listing_status']; ?>",
			listingUrl : "<?php echo $listings_url; ?>",
			listingExclUrl : "<?php echo $listings_excl_url; ?>",
			cycleFrequency : "<?php echo $instance['freq']; ?>"
		});
	});
})(csJQ);
</script>
<?php echo $after_widget; ?>
