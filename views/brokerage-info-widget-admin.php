<div class="<?php echo $this->id; ?>">
<?php
if($this->use_new_media_upload()) {
	$image_upload_iframe_src = "";
} else {
	$media_upload_iframe_src = "media-upload.php?type=image&context=".$this->id."&TB_iframe=true";
	$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
}
?>
  <p>
    <label for="<?php echo $this->get_field_id('name'); ?>">Brokerage Name</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" value="<?php echo $instance['name']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('logo_src'); ?>">Logo</label><br/>
    <select class="widefat" id="<?php echo $this->get_field_id('logo_src'); ?>" name="<?php echo $this->get_field_name('logo_src'); ?>" >
<?php foreach($brok_logos as $brok_logo) { 
        if($instance['logo_src'] == $brok_logo['src']) { ?>
      <option selected value="<?php echo $brok_logo['src']; ?>"><?php echo $brok_logo['name']; ?></option>
 <?php  } else { ?>
      <option value="<?php echo $brok_logo['src']; ?>"><?php echo $brok_logo['name']; ?></option>
<?php   }
      } ?>
    </select>
    <div id="display-<?php echo $this->get_field_id('logo_src'); ?>" style="text-align:center;"><?php 
if ($instance['logo_src'] == "upload_custom" && !empty($instance['upload_logo_src'])) echo "<img src=\"{$instance['upload_logo_src']}\" style=\"border:1px solid black;max-width: 250px;\" />";
else echo "<img src=\"{$instance['logo_src']}\" style=\"border:1px solid black;max-width: 250px;\" />";

?>  </div>
	<input type="hidden" id="<?php echo $this->get_field_id('upload_logo_src'); ?>" name="<?php echo $this->get_field_name('upload_logo_src'); ?>" value="<?php echo $instance['upload_logo_src']; ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('addr'); ?>">Address</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('addr'); ?>" name="<?php echo $this->get_field_name('addr'); ?>" value="<?php echo $instance['addr']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('phone'); ?>">Phone Number</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" value="<?php echo $instance['phone']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('fax'); ?>">Fax Number</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('fax'); ?>" name="<?php echo $this->get_field_name('fax'); ?>" value="<?php echo $instance['fax']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('email'); ?>">Email Address</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" value="<?php echo $instance['email']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('web'); ?>">Website URL</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('web'); ?>" name="<?php echo $this->get_field_name('web'); ?>" value="<?php echo $instance['web']; ?>"> 
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('text'); ?>">Extra Text</label><br/>
    <textarea class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $instance['text']; ?></textarea>
  </p>
  <script type="text/javascript">
	(function($){
		$(document).ready(function() {
			
			// Compile the options, they will be the same in structure for both our types of initialization.
			var default_opts = {
				"logo_src_id" : "<?php echo $this->get_field_id('logo_src'); ?>",
				"upload_logo_src_id" : "<?php echo $this->get_field_id('upload_logo_src'); ?>",
				"img_upload_src" : "<?php echo $image_upload_iframe_src; ?>",
				<?php if($this->use_new_media_upload()) { ?>"use_new_media_upload" : true, <?php } ?>
				"preview_img_cnt" : "display-<?php echo $this->get_field_id('logo_src'); ?>"
			};
			
			// Initialize the JavaScript -- This has to be done differently based on if this is the first adding of the widget or if the widget is already present in a sidebar. NOTE: if we're on the first add case then none of the $this->xyz values will be correct.
			if("<?php echo $this->id; ?>".match("__i__$") == null) { // Widget is already initialized, aka it has NOT just been added.
			
				$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csBrokerageInfoWidget(default_opts);
			} else { // Widget has just now been added to a sidebar.
				
				// Here we register an ajaxSuccess callback so that once the widget has been added we can initalize it properly once it has been added.
				// NOTE / WARNING - This will be registered once on the widgets page load and then again each time this widget is added -- so the initialization routine for the widget MUST be able to deal with being called more than once. (I know of no way to remove these callback functions once they have been added - EZ).
				jQuery(document).ajaxSuccess(function(e, xhr, settings) {
					var widget_id_base = 'cs-brokerage-info-widget';
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
						$('.'+widget_id+':not(div[class$="__i__"])').csBrokerageInfoWidget(opts);
					}
				});
			}
		});
	})(csJQ);
  </script>
</div>
