<div id="<?php echo $this->get_field_id("") ?>container" class="<?php echo $this->widget_options['classname'] ?>-container widget">

<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>

  <form id="<?php echo $this->get_field_id("listing_quick_search_form") ?>" class="searchform" method="get">
<?php echo    '<img src="' . plugins_url('images/se_MLS_logo_91x30.png', dirname(__FILE__) ) . '" style="vertical-align:middle"><input id="' . $this->get_field_id("search_text") . '" class="s" type="text" name="term" value="MLS#, Community, etc." />'?>
  </form>
</div>
<script>
(function($){
	$(document).ready(function(){
		$("#<?php echo $this->get_field_id("") ?>container").ListingQuickSearchWidget({
			ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
			idx_url : "<?php echo $idx_url ?>",
			listings_url : "<?php echo $listings_url ?>"
		});
	});
})(jQuery);
</script>