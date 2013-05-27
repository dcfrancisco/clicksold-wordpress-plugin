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
?>  <br /><?php
// we add the next line with an onclick event to ensure that this widget is loaded in the JS object. Widgets that are already 
?>    
    <a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" onclick="csJQ('.<?php echo $this->id; ?>').csPersonalProfileWidget('checkInit', null, function(){csJQ('#add_image-<?php echo $this->get_field_id('image'); ?>').click();});return false;" class="thickbox-personal-profile-widget" title='<?php echo $image_title; ?>' style="text-decoration:none"><img src='images/media-button-image.gif' alt='<?php echo $image_title; ?>' align="absmiddle" /> <?php echo $image_title; ?></a>
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
			$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csPersonalProfileWidget({
<?php if($this->use_new_media_upload()) { ?>
				"useNewMediaUploader" : true
<?php } ?>
			});
		});
	})(csJQ);
  </script>
</div>
