<div class="widget_brokerage_info">
  <p>
    <label for="<?php echo $this->get_field_id('name'); ?>">Brokerage Name</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('name'); ?>" name="<?php echo $this->get_field_name('name'); ?>" value="<?php echo $instance['name']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('logo_src'); ?>">Logo</label><br/>
    <select id="<?php echo $this->get_field_id('logo_src'); ?>" name="<?php echo $this->get_field_name('logo_src'); ?>" onchange="this.parentNode.nextElementSibling.firstChild.setAttribute('src', this.value);">
<?php foreach($brok_logos as $brok_logo) { 
        if($instance['logo_src'] == $brok_logo['src']) { ?>
      <option selected value="<?php echo $brok_logo['src']; ?>"><?php echo $brok_logo['name']; ?></option>
 <?php  } else { ?>
      <option value="<?php echo $brok_logo['src']; ?>"><?php echo $brok_logo['name']; ?></option>
<?php   }
      } ?>
    </select>
  </p>
  <div class="widget_brokerage_info_img"><img id="cs_brok_img_prev" src="<?php echo $instance['logo_src']; ?>" /></div>
  <p>
    <label for="<?php echo $this->get_field_id('addr'); ?>">Address</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('addr'); ?>" name="<?php echo $this->get_field_name('addr'); ?>" value="<?php echo $instance['addr']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('phone'); ?>">Phone Number</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" value="<?php echo $instance['phone']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('fax'); ?>">Fax Number</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('fax'); ?>" name="<?php echo $this->get_field_name('fax'); ?>" value="<?php echo $instance['fax']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('email'); ?>">Email Address</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" value="<?php echo $instance['email']; ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('web'); ?>">Website URL</label><br/>
    <input type="text" id="<?php echo $this->get_field_id('web'); ?>" name="<?php echo $this->get_field_name('web'); ?>" value="<?php echo $instance['web']; ?>"> 
  </p>
  <p>
    <label for="<?php echo $this->get_field_id('text'); ?>">Extra Text</label><br/>
    <textarea id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $instance['text']; ?></textarea>
  </p>
</div>