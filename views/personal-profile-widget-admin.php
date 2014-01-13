<div class="<?php echo $this->id; ?>">
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Your Name:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" /></p>
  
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
    <a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" class="thickbox-personal-profile-widget" title='<?php echo $image_title; ?>' style="text-decoration:none"><img src='images/media-button-image.gif' alt='<?php echo $image_title; ?>' align="absmiddle" /> <?php echo $image_title; ?></a>
    <div id="display-<?php echo $this->get_field_id('image'); ?>" style="text-align:center;"><?php 
if ($instance['imageurl']) {
	echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['title']}\" style=\"border:1px solid black;";
		if ($instance['width'] && is_numeric($instance['width'])) {
			echo "max-width: 224px;";
		}
		if ($instance['height'] && is_numeric($instance['height'])) {
			echo "max-height: {$instance['height']}px;";
		}
		echo "\" />";
}
?>  </div>
    <br clear="all" />
    <input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="hidden" value="<?php echo $instance['image']; ?>" />
  </p>
  <p><label for="<?php echo $this->get_field_id('width'); ?>">Width:</label>
    <input class="cs-personal-profile-widget-admin-width" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['width'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('height'); ?>">Height:</label>
    <input class="cs-personal-profile-widget-admin-height" id="<?php echo $this->get_field_id('height'); ?>" name="<?php echo $this->get_field_name('height'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['height'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('description'); ?>">Description (optional):</label>
    <textarea rows="8" class="widefat" id="<?php echo $this->get_field_id('description'); ?>" name="<?php echo $this->get_field_name('description'); ?>"><?php echo format_to_edit($instance['description']); ?></textarea></p>
  
  <p><label for="<?php echo $this->get_field_id('link'); ?>">Link to biography/contact (optional):</label>
    <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['link'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('phone'); ?>">Phone:</label>
    <input id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['phone'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('mobilePhone'); ?>">Mobile Phone:</label>
    <input id="<?php echo $this->get_field_id('mobilePhone'); ?>" name="<?php echo $this->get_field_name('mobilePhone'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['mobilePhone'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('fax'); ?>">Fax:</label>
    <input id="<?php echo $this->get_field_id('fax'); ?>" name="<?php echo $this->get_field_name('fax'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['fax'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('email'); ?>">Email:</label>
    <input id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['email'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('showIcons'); ?>">Show Icons:</label>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('showIcons'); ?>" name="<?php echo $this->get_field_name('showIcons'); ?>"<?php checked( $showIcons ); ?> /></p>
  <script type="text/javascript">
	(function($){
		$(document).ready(function() {

			// Initialize the JavaScript -- This has to be done differently based on if this is the first adding of the widget or if the widget is already present in a sidebar. NOTE: if we're on the first add case then none of the $this->xyz values will be correct.
			if("<?php echo $this->id; ?>".match("__i__$") == null) { // Widget is already initialized, aka it has NOT just been added.
			
				$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csPersonalProfileWidget({
					<?php if($this->use_new_media_upload()) { ?>
						"useNewMediaUploader" : true
					<?php } ?>
				});
			} else { // Widget has just now been added to a sidebar.
				
				// Here we register an ajaxSuccess callback so that once the widget has been added we can initalize it properly once it has been added.
				// NOTE / WARNING - This will be registered once on the widgets page load and then again each time this widget is added -- so the initialization routine for the widget MUST be able to deal with being called more than once. (I know of no way to remove these callback functions once they have been added - EZ).
				jQuery(document).ajaxSuccess(function(e, xhr, settings) {
					var widget_id_base = 'cs-widget-personal-profile';
					if(	settings.data.search('action=save-widget') != -1 &&				// Present on each widget save.
						settings.data.search('id_base=' + widget_id_base) != -1 &&		// Only proceed if the widget save is for one of *these* widgets (a random widget won't do).
						settings.data.search('add_new=multi') != -1) {					// This parameter is present when a widget is added to a sidebar but is not present when you click the save button.
	
						// Get the class name for this widget (which is used as it's unique identifier) eg: cs-community-search-widget-6
						var widget_id = settings.data.match(/widget-id=[^&]*/g);
						widget_id = widget_id[0].replace(/widget-id=/g, ''); // Clear the parameter name from the above match.
	
						// Initialize the widget based on the widget-id (the class name) that we were provided by the ajaxSuccess subsystem.
						$('.'+widget_id+':not(div[class$="__i__"])').csPersonalProfileWidget({
							<?php if($this->use_new_media_upload()) { ?>
								"useNewMediaUploader" : true
							<?php } ?>
						});
					}
				});
			}
		});
	})(csJQ);
  </script>
</div>
