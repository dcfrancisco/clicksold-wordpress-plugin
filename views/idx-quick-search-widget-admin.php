<div class="<?php echo $this->id; ?>">
  <p>
    <label for="<?php echo $this->get_field_id('default_prop_type'); ?>">Default Property Type</label><br/>
    <select class="widefat" id="<?php echo $this->get_field_id('default_prop_type'); ?>" name="<?php echo $this->get_field_name('default_prop_type'); ?>">
<?php foreach($prop_types as $prop_type) { 
        if($instance['default_prop_type'] == $prop_type['val']) { ?>
      <option selected value="<?php echo $prop_type['val']; ?>"><?php echo $prop_type['name']; ?></option>
 <?php  } else { ?>
      <option value="<?php echo $prop_type['val']; ?>"><?php echo $prop_type['name']; ?></option>
<?php   }
      } ?>
    </select>
  </p>
  
  <p>
    <label for="<?php echo $this->get_field_id('min_width'); ?>">Minimum Width (px)</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('min_width'); ?>" name="<?php echo $this->get_field_name('min_width'); ?>" value="<?php echo $instance['min_width']; ?>">
  </p>
  
  <p>
    <label for="<?php echo $this->get_field_id('min_height'); ?>">Minimum Height (px)</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('min_height'); ?>" name="<?php echo $this->get_field_name('min_height'); ?>" value="<?php echo $instance['min_height']; ?>">
  </p>
  
  <p>
    <label for="<?php echo $this->get_field_id('max_width'); ?>">Maximum Width (px)</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('max_width'); ?>" name="<?php echo $this->get_field_name('max_width'); ?>" value="<?php echo $instance['max_width']; ?>">
  </p>
  
  <p>
    <label for="<?php echo $this->get_field_id('max_height'); ?>">Maximum Height (px)</label><br/>
    <input class="widefat" type="text" id="<?php echo $this->get_field_id('max_height'); ?>" name="<?php echo $this->get_field_name('max_height'); ?>" value="<?php echo $instance['max_height']; ?>">
  </p>

  <p>
    <label for="<?php echo $this->get_field_id('compact_vers'); ?>">Compact</label><br/>
    <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id('compact_vers'); ?>" name="<?php echo $this->get_field_name('compact_vers'); ?>"<?php checked( $compact_vers ); ?> />
  </p>
  
</div>
