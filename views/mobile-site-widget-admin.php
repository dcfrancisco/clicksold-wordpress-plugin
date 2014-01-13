<div class="<?php echo $this->id; ?>">
  <p><label for="<?php echo $this->get_field_id('image'); ?>">Image:</label>
<?php
if($this->use_new_media_upload()) {
	$image_upload_iframe_src = "#";
} else {
	$media_upload_iframe_src = "media-upload.php?type=image&context=".$this->id."&TB_iframe=true";
	$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
}
$image_title = __(($instance['image'] ? 'Change Image' : 'Add Image'), $this->pluginDomain);
?>  <br />
	<input type="radio" name="<?php echo $this->get_field_name('imagetype'); ?>" value="default" <?php if($instance['imagetype'] == 'default') echo 'checked="checked"'; ?> > Default <br/>
	<input type="radio" name="<?php echo $this->get_field_name('imagetype'); ?>" value="custom" <?php if($instance['imagetype'] == 'custom') echo 'checked="checked"'; ?> > Custom&nbsp;&nbsp;&nbsp;<a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" class="thickbox-mobile-site-widget" title='<?php echo $image_title; ?>' style="text-decoration:none"><img src='images/media-button-image.gif' align="absmiddle" /> <?php echo $image_title; ?></a>
    <div id="display-<?php echo $this->get_field_id('image'); ?>" style="text-align:center;"><?php 
if ($instance['image']) {
	echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['alt_text']}\" style=\"border:1px solid black;max-width: 250px;\" />";
}
?>  </div>
    <br clear="all" />
    <input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="hidden" value="<?php echo $instance['image']; ?>" />
  </p>
  <p><label for="<?php echo $this->get_field_id('alt_text'); ?>">Alt Text:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('alt_text'); ?>" name="<?php echo $this->get_field_name('alt_text'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['alt_text'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('smallText'); ?>">Small Text:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('smallText'); ?>" name="<?php echo $this->get_field_name('smallText'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['smallText'])); ?>" /></p>

  <p><label for="<?php echo $this->get_field_id('largeText'); ?>">Large Text:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('largeText'); ?>" name="<?php echo $this->get_field_name('largeText'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['largeText'])); ?>" /></p>
	
  <script type="text/javascript">
	(function($){
		$(document).ready(function() {
			
			// Compile the options, they will be the same in structure for both our types of initialization.
			var default_opts = {
					"defaultImgUrl" : "<?php echo $this->default_img_url; ?>",
					"imgElemId" : "<?php echo $this->get_field_id('image'); ?>",
					<?php if($this->use_new_media_upload()) { ?>
						"useNewMediaUploader" : true,
					<?php } ?>
					"imgTypeName" : "<?php echo $this->get_field_name('imagetype'); ?>"
			};
			
			// Initialize the JavaScript -- This has to be done differently based on if this is the first adding of the widget or if the widget is already present in a sidebar. NOTE: if we're on the first add case then none of the $this->xyz values will be correct.
			if("<?php echo $this->id; ?>".match("__i__$") == null) { // Widget is already initialized, aka it has NOT just been added.
			
				$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csMobileSiteWidget(default_opts);
			} else { // Widget has just now been added to a sidebar.
				
				// Here we register an ajaxSuccess callback so that once the widget has been added we can initalize it properly once it has been added.
				// NOTE / WARNING - This will be registered once on the widgets page load and then again each time this widget is added -- so the initialization routine for the widget MUST be able to deal with being called more than once. (I know of no way to remove these callback functions once they have been added - EZ).
				jQuery(document).ajaxSuccess(function(e, xhr, settings) {
					var widget_id_base = 'cs-widget-mobile-site';
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
						$('.'+widget_id+':not(div[class$="__i__"])').csMobileSiteWidget(opts);
					}
				});
			}
		});
	})(csJQ);
  </script>
</div>
