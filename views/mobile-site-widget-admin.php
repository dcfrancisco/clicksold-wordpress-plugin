<div class="<?php echo $this->id; ?>">
  <p><label for="<?php echo $this->get_field_id('image'); ?>">Image:</label>
<?php
	$media_upload_iframe_src = "media-upload.php?type=image&widget_id=".$this->id; //NOTE #1: the widget id is added here to allow uploader to only return array if this is used with image widget so that all other uploads are not harmed.
	$image_upload_iframe_src = apply_filters('image_upload_iframe_src', "$media_upload_iframe_src");
	$image_title = __(($instance['image'] ? 'Change Image' : 'Add Image'), $this->pluginDomain);
?>  <br /><?php
// we add the next line with an onclick event to ensure that this widget is loaded in the JS object. Widgets that are already 
?>    
	<input type="radio" name="<?php echo $this->get_field_name('imagetype'); ?>" value="default" <?php if($instance['imagetype'] == 'default') echo 'checked="checked"'; ?>> Default <br/>
	<input type="radio" name="<?php echo $this->get_field_name('imagetype'); ?>" value="custom" <?php if($instance['imagetype'] == 'custom') echo 'checked="checked"'; ?>> Custom&nbsp;&nbsp;&nbsp;<a href="<?php echo $image_upload_iframe_src; ?>&TB_iframe=true" id="add_image-<?php echo $this->get_field_id('image'); ?>" onclick="jQuery('.<?php echo $this->id; ?>').csMobileSiteWidget('checkInit', null, function(){jQuery('#add_image-<?php echo $this->get_field_id('image'); ?>').click();});return false;" class="thickbox-mobile-site-widget" title='<?php echo $image_title; ?>' style="text-decoration:none"><img src='images/media-button-image.gif' align="absmiddle" /> <?php echo $image_title; ?></a>
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
			$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csMobileSiteWidget({
				"defaultImgUrl" : "<?php echo $this->default_img_url; ?>",
				"imgElemId" : "<?php echo $this->get_field_id('image'); ?>",
				"imgTypeName" : "<?php echo $this->get_field_name('imagetype'); ?>"
			});
		});
	})(jQuery);
  </script>
</div>