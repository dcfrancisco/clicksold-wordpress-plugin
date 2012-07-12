<div class="widget_vip">
  <p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('hideOpts'); ?>">Hide VIP Options when logged out</label><br/>
    <input type="checkbox" id="<?php echo $this->get_field_id('hideOpts'); ?>" name="<?php echo $this->get_field_name('hideOpts'); ?>" <?php checked($instance['hideOpts']) ?>>
  </p>
</div>