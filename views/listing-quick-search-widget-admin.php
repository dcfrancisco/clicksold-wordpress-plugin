<div class="widget_listing_quick_search">
  <p>
	<label for="<?php echo $this->get_field_id('title'); ?>">Title</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" class="widefat" value="<?php echo $instance['title']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('backgroundColor'); ?>">Background Color</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('backgroundColor'); ?>" name="<?php echo $this->get_field_name('backgroundColor'); ?>" class="widefat" value="<?php echo $instance['backgroundColor']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('backgroundOpacity'); ?>">Background Opacity</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('backgroundOpacity'); ?>" name="<?php echo $this->get_field_name('backgroundOpacity'); ?>" class="widefat" value="<?php echo $instance['backgroundOpacity']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('minWidth'); ?>">Min Container Width</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('minWidth'); ?>" name="<?php echo $this->get_field_name('minWidth'); ?>" class="widefat" value="<?php echo $instance['minWidth']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('maxWidth'); ?>">Max Container Width</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('maxWidth'); ?>" name="<?php echo $this->get_field_name('maxWidth'); ?>" class="widefat" value="<?php echo $instance['maxWidth']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('minHeight'); ?>">Min Container Height</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('minHeight'); ?>" name="<?php echo $this->get_field_name('minHeight'); ?>" class="widefat" value="<?php echo $instance['minHeight']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('maxHeight'); ?>">Max Container Height</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('maxHeight'); ?>" name="<?php echo $this->get_field_name('maxHeight'); ?>" class="widefat" value="<?php echo $instance['maxHeight']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('widgetHeight'); ?>">Widget Height (Vertical Adj)</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('widgetHeight'); ?>" name="<?php echo $this->get_field_name('widgetHeight'); ?>" class="widefat" value="<?php echo $instance['widgetHeight']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('top'); ?>">Top (Vertical Adj)</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('top'); ?>" name="<?php echo $this->get_field_name('top'); ?>" class="widefat" value="<?php echo $instance['top']; ?>"><br/>
	<label for="<?php echo $this->get_field_id('translateY'); ?>">Transform - translateY (Vertical Adj)</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('translateY'); ?>" name="<?php echo $this->get_field_name('translateY'); ?>" class="widefat" value="<?php echo $instance['translateY']; ?>"><br/><br/>
	<label for="<?php echo $this->get_field_id('showMLSSearchLogo'); ?>">Show MLS&reg; Search Logo</label>
    <input style="float:right;" type="checkbox" id="<?php echo $this->get_field_id('showMLSSearchLogo'); ?>" name="<?php echo $this->get_field_name('showMLSSearchLogo'); ?>" <?php checked($showMLSSearchLogo); ?>><br/>
	<label for="<?php echo $this->get_field_id('showSearchButton'); ?>">Show Search Button</label>
    <input style="float:right;" type="checkbox" id="<?php echo $this->get_field_id('showSearchButton'); ?>" name="<?php echo $this->get_field_name('showSearchButton'); ?>" <?php checked($showSearchButton); ?>><br/>
  </p>
</div>