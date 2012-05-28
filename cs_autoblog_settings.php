<?php
/*
* ClickSold Auto Blog Settings Page
*
* Copyright (C) 2012 ClickSold.com
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

require_once('../../../wp-load.php');
require_once('cs_constants.php');

$re_load_gen_set = false;
$re_load_act_set = false;
$re_load_sol_set = false;

/**
 * Renders an formatting options array as a 3 col table.
 */
function render_format_options_table( $format_options ) {

	echo '<table class="cs-ab-available-formats-table">';
	echo '  <tbody>';
	echo '    <tr>';

	$arr_counter = 0;
	foreach( $format_options as $key => $value) {
		$arr_counter++;

		echo ' <td>' . $key . ' : ' . $value . '</td>';

		if( ( $arr_counter % 3 ) == 0 ) {
			echo '</tr><tr>';
		}

	} 

	echo '    </tr>';
	echo '  <tbody>';
	echo '</table>';
	echo '';
}

/**
 * Renders the general settings form.
 */
function render_general_settings_form( $is_update = false ) {

	$cs_autoblog_new = get_option('cs_autoblog_new');
	$cs_autoblog_sold = get_option('cs_autoblog_sold');
	$cs_autoblog_freq = get_option('cs_autoblog_freq');

?>

<?php if(!$is_update){ ?>	  
  <div id="auto_blog_gen_update_div" class="cs-semiopacity">
<?php } ?>	  
    
	<form id="auto_blog_listings_settings" name="auto_blog_listings_settings" method="POST" action="<?php echo plugins_url( "cs_autoblog_settings.php", __FILE__ ); ?>" class="cs-form cs-form-inline">
	  <div class="cs-form-section">
	    <div class="cs-form-section-title">General Settings</div>
	    <fieldset>
          <div class="cs-input-small"><div class="cs-label-container"><label for="cs_autoblog_new">Post Newly Active Listings:</label></div><input type="checkbox" id="cs_autoblog_new" name="cs_autoblog_new" class="cs-checkbox" <?php if( $cs_autoblog_new == "1" ){ ?>checked="checked"<?php } ?> /><br/></div><div class="cs-input-small"><div class="cs-label-container"><label for="cs_autoblog_sold">Post Newly Sold Listings:</label></div><input type="checkbox" id="cs_autoblog_sold" name="cs_autoblog_sold" class="cs-checkbox" <?php if( $cs_autoblog_sold == "1" ){ ?>checked="checked"<?php } ?> /><br/></div>
		  <div class="cs-label-container"><label for="cs_autoblog_freq">Update Frequency</label></div>
          <div class="cs-input-container">
		    <div class="cs-adjust-for-box-model">
              <select name="cs_autoblog_freq" style="width:75px;">
                <option value="1"<?php if($cs_autoblog_freq == 1) { ?> selected="selected"<?php } ?>>1</option>
                <option value="3"<?php if($cs_autoblog_freq == 3) { ?> selected="selected"<?php } ?>>3</option>
                <option value="4"<?php if($cs_autoblog_freq == 4) { ?> selected="selected"<?php } ?>>4</option>
                <option value="7"<?php if($cs_autoblog_freq == 7) { ?> selected="selected"<?php } ?>>7</option>
                <option value="14"<?php if($cs_autoblog_freq == 14) { ?> selected="selected"<?php } ?>>14</option>
                <option value="30"<?php if($cs_autoblog_freq == 30) { ?> selected="selected"<?php } ?>>30</option>
              </select> Day(s)
			</div>
          </div>
		</fieldset>
		<div class="cs-form-submit-buttons-box">
		  <input type="hidden" name="upd_ab_gen_settings" value="true" />
		  <input type="submit" name="Submit" class="cs-button" value="<?php esc_attr_e('Save Changes') ?>" />
		</div>
	  </div>
<?php if($is_update){ ?>	  
	  <div class="cs-form-feedback"><?php _e('Updated'); ?></div>
<?php } ?>	  
    </form>
<?php if(!$is_update){ ?>	  
  </div>
<?php } ?>	  

<?php
}

/**
 * Renders the active (newly listed) listings settings form.
 */
function render_new_settings_form( $is_update = false ) {

	global $CS_VARIABLE_AUTO_BLOG_TITLE_LEGEND;
	global $CS_VARIABLE_AUTO_BLOG_CONTENT_LEGEND;

	global $cs_autoblog_default_post_title_active;   // Option names.
	global $cs_autoblog_default_post_content_active; //   "

	$cs_autoblog_new_title = get_option('cs_autoblog_new_title', $cs_autoblog_default_post_title_active );	// Defaults here are only used if someone deleted these options as they are created (if missing) on plugin activation.
	$cs_autoblog_new_content = stripslashes(get_option('cs_autoblog_new_content', $cs_autoblog_default_post_content_active ));

?>

<?php if(!$is_update){ ?>	  
  <div id="auto_blog_actives_update_div" class="cs-semiopacity">
<?php } ?>	  

    <form id="auto_blog_actives_settings" name="auto_blog_actives_settings" method="POST" action="<?php echo plugins_url( "cs_autoblog_settings.php", __FILE__ ) ?>" class="cs-form cs-form-inline">
      <div class="cs-form-section">
        <div class="cs-form-section-title">Auto Blogger - Newly Active Listings</div>
        <h3>Post Title:</h3>
        <h4>Options:</h4>

<?php   render_format_options_table( $CS_VARIABLE_AUTO_BLOG_TITLE_LEGEND ); ?>

        <input type="text" name="cs_autoblog_new_title" value="<?php echo cs_encode_for_html( $cs_autoblog_new_title ) ?>" />
        <h3>Post Content:</h3>
        <h4>Options:</h4>

<?php   render_format_options_table( $CS_VARIABLE_AUTO_BLOG_CONTENT_LEGEND ); ?>

<?php
    // TODO: Replace textarea with this when we figure out how to add the proper css/js includes
	//$editor_args = array( media_buttons => false, textarea_name => 'activepostcontent');
	//wp_editor('', 'activepostcontent', $editor_args); 
?>
        <textarea name="cs_autoblog_new_content" rows="10"><?php echo cs_encode_for_html( $cs_autoblog_new_content ) ?></textarea>
        <div class="cs-form-submit-buttons-box">
          <input type="hidden" name="upd_ab_act_settings" value="true" />
          <input type="submit" name="Submit" class="cs-button" value="<?php esc_attr_e('Save Changes') ?>" />
        </div>
      </div>
<?php if($is_update){ ?>	  
      <div class="cs-form-feedback"><?php _e('Updated'); ?></div>
<?php } ?>	
    </form>
<?php if(!$is_update){ ?>	  
  </div>
<?php } ?>	  


<?php
}

/**
 * Renders the sold listings settings form.
 */
function render_sold_settings_form( $is_update = false ) {

	global $CS_VARIABLE_AUTO_BLOG_TITLE_LEGEND;
	global $CS_VARIABLE_AUTO_BLOG_CONTENT_LEGEND;

	global $cs_autoblog_default_post_title_sold;     // Option names.
	global $cs_autoblog_default_post_content_sold;   //   "

	$cs_autoblog_sold_title = get_option('cs_autoblog_sold_title', $cs_autoblog_default_post_title_sold ); // Defaults here are only used if someone deleted these options as they are created (if missing) on plugin activation.
	$cs_autoblog_sold_content = stripslashes(get_option('cs_autoblog_sold_content', $cs_autoblog_default_post_content_sold ));

?>

<?php if(!$is_update){ ?>	  
  <div id="auto_blog_solds_update_div" class="cs-semiopacity">
<?php } ?>	  

    <form id="auto_blog_solds_settings" name="auto_blog_solds_settings" method="POST" action="<?php echo plugins_url( "cs_autoblog_settings.php", __FILE__ ) ?>" class="cs-form cs-form-inline">
      <div class="cs-form-section">
        <div class="cs-form-section-title">Auto Blogger - Newly Sold Listings</div>
        <h3>Post Title:</h3>
        <h4>Options:</h4>

<?php   render_format_options_table( $CS_VARIABLE_AUTO_BLOG_TITLE_LEGEND ); ?>

        <input type="text" name="cs_autoblog_sold_title" value="<?php echo cs_encode_for_html( $cs_autoblog_sold_title ) ?>" />
        <h3>Post Content:</h3>
        <h4>Options:</h4>

<?php   render_format_options_table( $CS_VARIABLE_AUTO_BLOG_CONTENT_LEGEND ); ?>

<?php
	//$editor_args = array( media_buttons => false, textarea_name => 'soldpostcontent');
	//wp_editor('', 'soldpostcontent', $editor_args); 
?>
        <textarea name="cs_autoblog_sold_content" rows="10"><?php echo cs_encode_for_html( $cs_autoblog_sold_content ) ?></textarea>
        <div class="cs-form-submit-buttons-box">
          <input type="hidden" name="upd_ab_sol_settings" value="true" />
          <input type="submit" name="Submit" class="cs-button" value="<?php esc_attr_e('Save Changes') ?>" />
        </div>
      </div>
<?php if($is_update){ ?>	  
      <div class="cs-form-feedback"><?php _e('Updated'); ?></div>
<?php } ?>	
    </form>
<?php if(!$is_update){ ?>	  
  </div>
<?php } ?>	  

<?php
}


/**
 * Process the post submission.
 */
if( isset($_POST['upd_ab_gen_settings']) ){  //General Settings Update

	$re_load_gen_set = true;
	
	if( isset($_POST['cs_autoblog_new']) ) $cs_autoblog_new = "1";
	else $cs_autoblog_new = "0";
	
	if( isset($_POST['cs_autoblog_sold']) ) $cs_autoblog_sold = "1";
	else $cs_autoblog_sold = "0";
	
	$cs_autoblog_freq = $_POST['cs_autoblog_freq'];
	update_option('cs_autoblog_new', $cs_autoblog_new);
	update_option('cs_autoblog_sold', $cs_autoblog_sold);
	update_option('cs_autoblog_freq', $cs_autoblog_freq);
	
} else if( isset($_POST['upd_ab_act_settings']) ){  //New Listings Post Update

	$re_load_act_set = true;
	
	$cs_autoblog_new_title = $_POST['cs_autoblog_new_title'];
	$cs_autoblog_new_content = stripslashes($_POST['cs_autoblog_new_content']);
	update_option('cs_autoblog_new_title', $cs_autoblog_new_title);
	update_option('cs_autoblog_new_content', $cs_autoblog_new_content);
	
} else if( isset($_POST['upd_ab_sol_settings']) ){  //Sold Listings Post Update

	$re_load_sol_set = true;
	
	$cs_autoblog_sold_title = $_POST['cs_autoblog_sold_title'];
	$cs_autoblog_sold_content = stripslashes($_POST['cs_autoblog_sold_content']);
	update_option('cs_autoblog_sold_title', $cs_autoblog_sold_title);
	update_option('cs_autoblog_sold_content', $cs_autoblog_sold_content);
}


/**
 * Render the form components or the full form.
 */
if($re_load_gen_set == true) {

	render_general_settings_form( true /*updated?*/ );
} else if($re_load_act_set == true){

	render_new_settings_form( true /*updated?*/ );
} else if($re_load_sol_set == true) {

	render_sold_settings_form( true /*updated?*/ );
} else { // Load the entire form.
?>
<div id="ws_auto_blog_settings" class="cs_module">
<?php
	
	render_general_settings_form( false /*updated?*/ );
	render_new_settings_form( false /*updated?*/ );
	render_sold_settings_form( false /*updated?*/ );

?>
</div>
<?php
}

?>
<script type="text/javascript">
	$(document).ready(function(){

		// Routine checks any of the new(title / content) and sold(title / content) for blank values
		// and warns the user that they have saved settings that will prevent whichever section from
		// working.
		//
		// NOTE: This routine treats the form as a whole although it is made up of different parts.
		//
		var checkForBlankFields = function() {

			var newTitle = $("[name='cs_autoblog_new_title']").val();
			var newContent = $("[name='cs_autoblog_new_content']").val();
			var soldTitle = $("[name='cs_autoblog_sold_title']").val();
			var soldContent = $("[name='cs_autoblog_sold_content']").val();

			// Aggragate our info a bit (Warnings issued if the option to blog new / sold listings is enabled but one of the corresponding fields is blank)
			var needNewWarning = ( $('#cs_autoblog_new').is(':checked') && (newTitle == '' || newContent == ''));
			var needSoldWarning = ( $('#cs_autoblog_sold').is(':checked') && (soldTitle == '' || soldContent == ''));

			if(needNewWarning && needSoldWarning) {
	
				alert("Warning: Auto blogger is enabled for both new and sold listings but\n   one of the title / content template in each section is blank. The auto blogger\n   will not work until you fill in all of these fields.");
			} else if(needNewWarning) {

				alert("Warning: Auto blogger is enabled for new listings but\n   one of the title / content is blank. The auto blogger\n   will not work until you fill in both of these fields.");
			} else if(needSoldWarning) {

				alert("Warning: Auto blogger is enabled for sold listings but\n   one of the title / content is blank. The auto blogger\n   will not work until you fill in both of these fields.");
			}
		};

		// Used to clear the update messages from all of the form sections before we submit a section.
		var clearUpdatedMessages = function() {

			$('.cs-form-feedback').each(function () {
				$(this).fadeOut();
			});
		};

		$("#auto_blog_listings_settings").clickSoldUtils("csBindToForm", {
			"updateDivId" : "auto_blog_gen_update_div",
			"loadingDivId" : "null",
			"beforeSubmit" : function() { checkForBlankFields(); clearUpdatedMessages() }, // Runs the check just in case we turn one of these on.
			"plugin" : true
		});

		$("#auto_blog_actives_settings").clickSoldUtils("csBindToForm", {
			"updateDivId" : "auto_blog_actives_update_div",
			"loadingDivId" : "null",
			"beforeSubmit" : function() { checkForBlankFields(); clearUpdatedMessages() },
			"plugin" : true
		});
		
		$("#auto_blog_solds_settings").clickSoldUtils("csBindToForm", {
			"updateDivId" : "auto_blog_solds_update_div",
			"loadingDivId" : "null",
			"beforeSubmit" : function() { checkForBlankFields(); clearUpdatedMessages() },
			"plugin" : true
		});
	});
</script>