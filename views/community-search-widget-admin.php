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
		if("<?php echo $this->id; ?>".match("__i__$") == null) {
			$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csCommunitySearchWidgetAdmin({
				ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
				cities_id : "<?php echo $this->get_field_id('cities'); ?>",
				widget_id : "<?php echo $this->id; ?>"
			});
		}
	});
})(csJQ);
</script>