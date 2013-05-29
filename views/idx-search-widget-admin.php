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
?>  <br /><?php
// we add the next line with an onclick event to ensure that this widget is loaded in the JS object. Widgets that are already 
?>    
	<input type="radio" name="<?php echo $this->get_field_name('imagetype'); ?>" value="default" <?php if($instance['imagetype'] == 'default') echo 'checked="checked"'; ?> onclick="initUnsavedIDXSearchWidget(this, 'click')"> Default <br/>
	<input type="radio" name="<?php echo $this->get_field_name('imagetype'); ?>" value="custom" <?php if($instance['imagetype'] == 'custom') echo 'checked="checked"'; ?> onclick="initUnsavedIDXSearchWidget(this, 'click')"> Custom&nbsp;&nbsp;&nbsp;<a href="<?php echo $image_upload_iframe_src; ?>" id="add_image-<?php echo $this->get_field_id('image'); ?>" onclick="csJQ('.<?php echo $this->id; ?>').csIdxSearchWidget('checkInit', null, function(){csJQ('#add_image-<?php echo $this->get_field_id('image'); ?>').click();});return false;" class="thickbox-idx-search-widget" title='<?php echo $image_title; ?>' style="text-decoration:none"><img src='images/media-button-image.gif' alt='<?php echo $image_title; ?>' align="absmiddle" /> <?php echo $image_title; ?></a>
    <div id="display-<?php echo $this->get_field_id('image'); ?>" style="text-align:center;"><?php 
if (!empty($instance['image'])) {
	echo "<img src=\"{$instance['imageurl']}\" alt=\"{$instance['alt_text']}\" style=\"border:1px solid black;max-width: 250px;\" />";
}
?>  </div>
    <br clear="all" />
    <input id="<?php echo $this->get_field_id('image'); ?>" name="<?php echo $this->get_field_name('image'); ?>" type="hidden" value="<?php echo $instance['image']; ?>" />
  </p>
  <p><label for="<?php echo $this->get_field_id('link'); ?>">Image Link:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['link'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('alt_text'); ?>">Alt Text:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('alt_text'); ?>" name="<?php echo $this->get_field_name('alt_text'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['alt_text'])); ?>" /></p>
  
  <p><label for="<?php echo $this->get_field_id('smallText'); ?>">Small Text:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('smallText'); ?>" name="<?php echo $this->get_field_name('smallText'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['smallText'])); ?>" /></p>

  <p><label for="<?php echo $this->get_field_id('largeText'); ?>">Large Text:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('largeText'); ?>" name="<?php echo $this->get_field_name('largeText'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['largeText'])); ?>" /></p>
  <script type="text/javascript">
	(function($){
		$(document).ready(function() {
			if("<?php echo $this->id; ?>".match("__i__$") == null) {
				$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csIdxSearchWidget({
					"defaultImgUrl" : "<?php echo $this->default_img_url; ?>",
					"imgElemId" : "<?php echo $this->get_field_id('image'); ?>",
	<?php if($this->use_new_media_upload()) { ?>
					"useNewMediaUploader" : true,
	<?php } ?>
					"imgTypeName" : "<?php echo $this->get_field_name('imagetype'); ?>"
				});
			}
		});
	})(csJQ);
	
	function initUnsavedIDXSearchWidget(self, eventType){
		(function($){ 
			if("<?php echo $this->id; ?>".match("__i__$") != null) {
				//Widget has been added to a sidebar (not saved yet) - extract the id
				var bi_w = $(self).parent().parent().attr("class");
				var bi_w_id = bi_w.split("-");
				bi_w_id = bi_w_id[bi_w_id.length - 1];
				
				var opts = {
					"defaultImgUrl" : "<?php echo $this->default_img_url; ?>",
					"imgElemId" : "<?php echo $this->get_field_id('image'); ?>",
			<?php if($this->use_new_media_upload()) { ?>
					"useNewMediaUploader" : true,
			<?php } ?>		
					"imgTypeName" : "<?php echo $this->get_field_name('imagetype'); ?>"
				};
					
				//modify the opts - replace "__i__" with the found id
				for(var key in opts) {
					if(typeof opts[key] == "string") opts[key] = opts[key].replace(/__i__/g, bi_w_id);
				}
				
				$("." + bi_w + ":not(div[class$='__i__'])").csIdxSearchWidget("checkInit", opts, function(){
					// Trigger the original event that called this function
					$(self).trigger(eventType);  
				}); 
			}
		})(csJQ);
	}
  </script>
</div>
