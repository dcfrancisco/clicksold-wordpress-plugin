<div class="widget_brokerage_info">
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr(strip_tags($instance['title'])); ?>" />
  </p>
<?php if($this->BROKERAGE === false){ ?>
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
<?php } ?> 
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
    <label for="<?php echo $this->get_field_id('freq'); ?>">Cycle Frequency (min 1000 ms)</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('freq'); ?>" name="<?php echo $this->get_field_name('freq'); ?>" value="<?php echo $instance['freq']; ?>">
  </p>
</div>