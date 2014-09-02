<div class="<?php echo $this->id; ?>">
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('listing_section'); ?>"><?php echo $listing_section_label; ?></label><br/>
	<select class="widefat" id="<?php echo $this->get_field_id('listing_section'); ?>" name="<?php echo $this->get_field_name('listing_section'); ?>">
<?php   foreach($PLUGIN_FEAT_LIST_OPTS['listing_section']['values'] as $value) { 
          if($instance['listing_section'] == $value['opt_val']) { ?>
      <option selected value="<?php echo $value['opt_val']; ?>"><?php echo $value['opt_name']; ?></option>
 <?php    } else { ?>
      <option value="<?php echo $value['opt_val']; ?>"><?php echo $value['opt_name']; ?></option>
<?php     }
        } ?>
    </select>
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('listing_type'); ?>"><?php echo $listing_type_label; ?></label><br/>
	<select class="widefat" id="<?php echo $this->get_field_id('listing_type'); ?>" name="<?php echo $this->get_field_name('listing_type'); ?>">
<?php foreach($PLUGIN_FEAT_LIST_OPTS['listing_type']['values'] as $value) { 
        if($instance['listing_type'] == $value['opt_val']) { ?>
      <option selected value="<?php echo $value['opt_val']; ?>"><?php echo $value['opt_name']; ?></option>
 <?php  } else { ?>
      <option value="<?php echo $value['opt_val']; ?>"><?php echo $value['opt_name']; ?></option>
<?php   }
      } ?>
    </select>
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('listing_status'); ?>"><?php echo $listing_status_label; ?></label><br/>
	<select class="widefat" id="<?php echo $this->get_field_id('listing_status'); ?>" name="<?php echo $this->get_field_name('listing_status'); ?>">
<?php foreach($PLUGIN_FEAT_LIST_OPTS['listing_status']['values'] as $value) { 
        if($instance['listing_status'] == $value['opt_val']) { ?>
      <option selected value="<?php echo $value['opt_val']; ?>"><?php echo $value['opt_name']; ?></option>
 <?php  } else { ?>
      <option value="<?php echo $value['opt_val']; ?>"><?php echo $value['opt_name']; ?></option>
<?php   }
      } ?>
    </select>
  </p>
  
  <p>
    <label for="<?php echo $this->get_field_id('freq'); ?>">Cycle Frequency (min 1000 ms)</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('freq'); ?>" name="<?php echo $this->get_field_name('freq'); ?>" value="<?php echo $instance['freq']; ?>" />
  </p>

  <div id="user_defined_listings_select" style="display:none;">
    <p>
      <label for="userDefinedListingsType">Listing Type</label><br/>
      <select class="widefat" id="userDefinedListingsType">
<?php if($this->BROKERAGE == false) { ?>	  
	    <option value="0">Personal Actives</option>
	    <option value="1">Personal Solds</option>
<?php } 
      if($this->IDX == true) { ?>		
	    <option value="2">Office Actives</option>
<?php } ?>
	  </select>
      <div style="width:100%;">
        <div style="max-width:115px;width:100%;float:left;">
	      <span style="text-align:center;">Available</span>
	      <select id="listings_avail" size="7" multiple style="width:100%;"></select>
	    </div>
        <div style="width:30px;float:left;padding-left:6px;padding-right:6px;padding-top:40px;">
	      <input id="remFromSelected" type="button" value="<<" />
	      <input id="addToSelected" type="button" value=">>" />
	    </div>
	    <div style="max-width:115px;width:100%;float:left;">
	      <span style="text-align:center;">Selected</span>
	      <select id="<?php echo $this->get_field_id('user_defined_listings'); ?>" name="<?php echo $this->get_field_name('user_defined_listings'); ?>[]" size="7" multiple style="width:100%;">
<?php if(!empty($instance['user_defined_listings'])) {
        foreach($instance['user_defined_listings'] as $mlsNum) { ?>
            <option value="<?php echo $mlsNum ?>"><?php echo $mlsNum ?></option>
<?php   } 
      }?>
		  </select>
	    </div>
	  </div>
    </p>
  </div>
</div>
<br/>
<script type="text/javascript">
(function($){
	$(document).ready(function() {

		// Compile the options, they will be the same in structure for both our types of initialization.
		var default_opts = {
				ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
				listing_section_id : "<?php echo $this->get_field_id('listing_section'); ?>",
				user_defined_listings_id : "<?php echo $this->get_field_id('user_defined_listings'); ?>",
				widget_id : "<?php echo $this->id; ?>"
		};
		
		// Initialize the JavaScript -- This has to be done differently based on if this is the first adding of the widget or if the widget is already present in a sidebar. NOTE: if we're on the first add case then none of the $this->xyz values will be correct.
		if("<?php echo $this->id; ?>".match("__i__$") == null) { // Widget is already initialized, aka it has NOT just been added.
		
			$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csFeatureListingWidgetAdmin(default_opts);
		} else { // Widget has just now been added to a sidebar.
			
			// Here we register an ajaxSuccess callback so that once the widget has been added we can initalize it properly once it has been added.
			// NOTE / WARNING - This will be registered once on the widgets page load and then again each time this widget is added -- so the initialization routine for the widget MUST be able to deal with being called more than once. (I know of no way to remove these callback functions once they have been added - EZ).
			jQuery(document).ajaxSuccess(function(e, xhr, settings) {
				var widget_id_base = 'cs-feature-listing-widget';
				if(	settings.data.search('action=save-widget') != -1 &&				// Present on each widget save.
					settings.data.search('id_base=' + widget_id_base) != -1 &&		// Only proceed if the widget save is for one of *these* widgets (a random widget won't do).
					settings.data.search('add_new=multi') != -1) {					// This parameter is present when a widget is added to a sidebar but is not present when you click the save button.
	
					// Get the class name for this widget (which is used as it's unique identifier) eg: cs-community-search-widget-6
					var widget_id = settings.data.match(/widget-id=[^&]*/g);
					widget_id = widget_id[0].replace(/widget-id=/g, ''); // Clear the parameter name from the above match.
	
					// Now out of that we need to get the widget numeric id so we can update the options array correctly.
					var widget_id_numeric = widget_id.split("-");
					widget_id_numeric = widget_id_numeric[widget_id_numeric.length - 1];

					// Clone the opts object as updating it here would update it for any other of this widget that we are adding.
					var opts = jQuery.extend({}, default_opts);
	
					//modify the opts - replace "__i__" with the found id
					for(var key in opts) {
						if(typeof opts[key] == "string") opts[key] = opts[key].replace(/__i__/g, widget_id_numeric);
					}

					// Initialize the widget based on the widget-id (the class name) that we were provided by the ajaxSuccess subsystem.
					$('.'+widget_id+':not(div[class$="__i__"])').csFeatureListingWidgetAdmin(opts);
				}
			});
		}
	});
})(csJQ);
</script>
