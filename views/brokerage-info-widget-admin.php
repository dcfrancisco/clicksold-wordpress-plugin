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
    <select class="widefat" id="<?php echo $this->get_field_id('logo_src'); ?>" name="<?php echo $this->get_field_name('logo_src'); ?>" onchange="initUnsavedBrokInfoWidget(this, 'change')" >
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
			if("<?php echo $this->id; ?>".match("__i__$") == null) {
				$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csBrokerageInfoWidget({
					"logo_src_id" : "<?php echo $this->get_field_id('logo_src'); ?>",
					"upload_logo_src_id" : "<?php echo $this->get_field_id('upload_logo_src'); ?>",
					"img_upload_src" : "<?php echo $image_upload_iframe_src; ?>",
			<?php if($this->use_new_media_upload()) { ?>"use_new_media_upload" : true, <?php } ?>
					"preview_img_cnt" : "display-<?php echo $this->get_field_id('logo_src'); ?>"
				});
			}
		});
	})(csJQ);
	
	function initUnsavedBrokInfoWidget(self, eventType){
		(function($){ 
			if("<?php echo $this->id; ?>".match("__i__$")) {
				//Widget has been added to a sidebar (not saved yet) - extract the id
				var bi_w = $(self).parent().parent().attr("class");
				var bi_w_id = bi_w.split("-");
				bi_w_id = bi_w_id[bi_w_id.length - 1];
					
				var opts = {
					"logo_src_id" : "<?php echo $this->get_field_id('logo_src'); ?>",
					"upload_logo_src_id" : "<?php echo $this->get_field_id('upload_logo_src'); ?>",
					"img_upload_src" : "<?php echo $image_upload_iframe_src; ?>",
			<?php if($this->use_new_media_upload()) { ?>"use_new_media_upload" : true, <?php } ?>
					"preview_img_cnt" : "display-<?php echo $this->get_field_id('logo_src'); ?>"
				};
					
				//modify the opts - replace "__i__" with the found id
				for(var key in opts) {
					if(typeof opts[key] == "string") opts[key] = opts[key].replace(/__i__/g, bi_w_id);
				}
				
				$("." + bi_w + ":not(div[class$='__i__'])").csBrokerageInfoWidget("checkInit", opts, function(){
					/* NOTE: this is for firing the change event after initializing the change event itself - IE7 & IE8 will automatically
					   fire the event after the change event is initialized so we need to prevent it from being fired twice. */
					//$("#" + opts.logo_src_id, "." + bi_w).change();
					$(self).trigger(eventType);
				}); 
			}
		})(csJQ);
	}
  </script>
</div>
