<div class="<?php echo $this->id; ?>">
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" />
  </p>

  <div id="cities_select">
    <p>
      <div style="width:225px;overflow:auto;">
        <div style="width:90px;float:left;">
	      <span style="text-align:center;">Available</span>
	      <select id="cities_avail" size="7" multiple style="width:90px;">
<?php echo $city_list_avail ?>	
		  </select>
	    </div>
        <div style="width:30px;float:left;padding-left:6px;padding-right:6px;padding-top:40px;">
	      <input id="remFromSelected" type="button" value="<<" />
	      <input id="addToSelected" type="button" value=">>" />
	    </div>
	    <div style="width:90px;float:left;">
	      <span style="text-align:center;">Selected</span>
	      <select id="<?php echo $this->get_field_id('cities'); ?>" name="<?php echo $this->get_field_name('cities'); ?>[]" size="7" multiple style="width:90px;">
<?php if(!empty($instance['cities'])) {
        foreach($instance['cities'] as $city) { ?>
            <option title="<?php echo $city ?>" value="<?php echo $city ?>"><?php echo $city ?></option>
<?php   } 
      }?>
		  </select>
	    </div>
	  </div>
    </p>
  </div>
  
  <p>
    <label for="<?php echo $this->get_field_id('incOrExcSelected'); ?>">Include / Exclude Selected Cities</label><br/>
    <input id="widget-<?php echo $this->id; ?>-inc" class="radio" type="radio" name="<?php echo $this->get_field_name('incOrExcSelected'); ?>" value="0" <?php if($instance['incOrExcSelected'] == 0) { ?>checked<?php } ?> /> <span>Include</span><br/>
    <input id="widget-<?php echo $this->id; ?>-exc" class="radio" type="radio" name="<?php echo $this->get_field_name('incOrExcSelected'); ?>" value="1" <?php if($instance['incOrExcSelected'] == 1) { ?>checked<?php } ?> /> <span>Exclude</span>
  </p>
  
</div>
<script type="text/javascript">
(function($){
	$(document).ready(function() {
		
		// Initialize the JavaScript -- This has to be done differently based on if this is the first adding of the widget or if the widget is already present in a sidebar. NOTE: if we're on the first add case then none of the $this->xyz values will be correct.
		if("<?php echo $this->id; ?>".match("__i__$") == null) { // Widget is already initialized, aka it has NOT just been added.
		
			$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csCommunitySearchWidgetAdmin({
				ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
				cities_id : "<?php echo $this->get_field_id('cities'); ?>",
				widget_id : "<?php echo $this->id; ?>"
			});
		} else { // Widget has just now been added to a sidebar.
			
			// Here we register an ajaxSuccess callback so that once the widget has been added we can initalize it properly once it has been added.
			// NOTE / WARNING - This will be registered once on the widgets page load and then again each time this widget is added -- so the initialization routine for the widget MUST be able to deal with being called more than once. (I know of no way to remove these callback functions once they have been added - EZ).
			jQuery(document).ajaxSuccess(function(e, xhr, settings) {
				var widget_id_base = 'cs-community-search-widget';
				if(	settings.data.search('action=save-widget') != -1 &&				// Present on each widget save.
					settings.data.search('id_base=' + widget_id_base) != -1 &&		// Only proceed if the widget save is for one of *these* widgets (a random widget won't do).
					settings.data.search('add_new=multi') != -1) {					// This parameter is present when a widget is added to a sidebar but is not present when you click the save button.

					// Get the class name for this widget (which is used as it's unique identifier) eg: cs-community-search-widget-6
					var widget_id = settings.data.match(/widget-id=[^&]*/g);
					widget_id = widget_id[0].replace(/widget-id=/g, ''); // Clear the parameter name from the above match.

					// Initialize the widget based on the widget-id (the class name) that we were provided by the ajaxSuccess subsystem.
					$('.'+widget_id+':not(div[class$="__i__"])').csCommunitySearchWidgetAdmin({
						ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
						cities_id : "widget-" + widget_id + "-cities",
						widget_id : widget_id
					});
				}
			});
			
		}
	});
})(csJQ);
</script>
