<div class="<?php echo $this->id; ?>">
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" />
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('listing_section'); ?>"><?php echo $listing_section_label; ?></label><br/>
	<select id="<?php echo $this->get_field_id('listing_section'); ?>" name="<?php echo $this->get_field_name('listing_section'); ?>">
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
	<select id="<?php echo $this->get_field_id('listing_type'); ?>" name="<?php echo $this->get_field_name('listing_type'); ?>">
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
	<select id="<?php echo $this->get_field_id('listing_status'); ?>" name="<?php echo $this->get_field_name('listing_status'); ?>">
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
    <input type="text" id="<?php echo $this->get_field_id('freq'); ?>" name="<?php echo $this->get_field_name('freq'); ?>" value="<?php echo $instance['freq']; ?>" />
  </p>

  <div id="user_defined_listings_select" style="display:none;">
    <p>
      <label for="userDefinedListingsType">Listing Type</label>
      <select id="userDefinedListingsType">
<?php if($this->BROKERAGE == false) { ?>	  
	    <option value="0">Personal Actives</option>
	    <option value="1">Personal Solds</option>
<?php } 
      if($this->IDX == true) { ?>		
	    <option value="2">Office Actives</option>
<?php } ?>
	  </select>
      <div style="width:225px;overflow:auto;">
        <div style="width:90px;float:left;">
	      <span style="text-align:center;">Available</span>
	      <select id="listings_avail" size="7" multiple style="width:90px;"></select>
	    </div>
        <div style="width:30px;float:left;padding-left:6px;padding-right:6px;padding-top:40px;">
	      <input id="remFromSelected" type="button" value="<<" />
	      <input id="addToSelected" type="button" value=">>" />
	    </div>
	    <div style="width:90px;float:left;">
	      <span style="text-align:center;">Selected</span>
	      <select id="<?php echo $this->get_field_id('user_defined_listings'); ?>" name="<?php echo $this->get_field_name('user_defined_listings'); ?>[]" size="7" multiple style="width:90px;">
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
<script type="text/javascript">
(function($){
	$(document).ready(function() {
		if("<?php echo $this->id; ?>".match("__i__$") == null) {
			$('.<?php echo $this->id; ?>:not(div[class$="__i__"])').csFeatureListingWidgetAdmin({
				ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
				listing_section_id : "<?php echo $this->get_field_id('listing_section'); ?>",
				user_defined_listings_id : "<?php echo $this->get_field_id('user_defined_listings'); ?>",
				widget_id : "<?php echo $this->id; ?>"
			});
		}
	});
})(csJQ);
</script>