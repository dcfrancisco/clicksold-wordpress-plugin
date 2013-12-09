<?php 
echo $before_widget; 
if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
?>
<div id="<?php echo $this->get_field_id("") ?>container" class="<?php echo $this->widget_options['classname'] ?>-container widget cs-widget">
  <div class="csCommunitySearchModule" class="widgetContent">
    <div class="csCommunitySearchWidget">
	  <form id="<?php echo $this->get_field_id("") ?>_Form">
	    <div class="cs-label-container">
		  <label for="cities">City: </label>
		</div>
		<div class="cs-input-container">
		  <select id="cities" style="width:100%">
<?php 
	  foreach($city_list as $city) { ?>
            <option value="<?php echo $city ?>"><?php echo $city ?></option>
<?php } ?>
		  </select>
		</div>
		<br/>
		<div class="cs-label-container">
		  <label for="Neighborhood">Neighborhood</label>
		</div>
		<div class="cs-input-container">
		  <select id="neighbourhoods" style="width:100%"></select>
		</div>
		<br/>
        <input class="widget_form_submit" type="button" name="submit" value="Search" style="width:100%;"/>
	  </form>
    </div>
  </div>
</div>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$("#<?php echo $this->get_field_id("") ?>container").CommunitySearchWidget({
			plugin : true,
			ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
			linkFormat : "<?php echo $community_url ?>",
<?php if(!empty($regions)) { ?>
			regions : {
<?php   $index = 0;
        foreach($regions as $city => $neigh) {
          if($index == count($regions) - 1) { ?>
				"<?php echo $city ?>" : ["<?php echo implode("\", \"", $neigh) ?>"]
<?php     } else { ?>
				"<?php echo $city ?>" : ["<?php echo implode("\", \"", $neigh) ?>"],
<?php     } 
          $index++; ?>
<?php   } ?>
			}
<?php } ?>
		});
	});
})(csJQ);
</script>
<?php echo $after_widget; ?>
