<?php
/*
* ClickSold < SEO Settings Page
* Generates the page options for setting the meta title and description for generated pages (MLS Search, Listings, Communities and (possibly) Associates).  Used in the ClickSold - Website Settings.
* 
* If not processing an update all the available forms are rendered otherwise the update is processed and only the updated
* form is rendered such that can be used as part of an ajax include.
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

/**
 * Returns the options legend constant (the options available for use in the replacement template.
 * based on the 'prefix' of the given page.
 *
 * param - prefix - used to identify which page we're talking about.
 * param - for_content_section - for listing and for assoicates pages we add an extra parameter each that's not contained in the constants.
 *                               These are the listing description and the associate description (profile).
 */
function get_options_legend( $prefix, $for_content_section = false ) {

	global $CS_VARIABLE_LISTING_META_TITLE_VARS_LEGEND;
	global $CS_VARIABLE_COMMUNITY_META_TITLE_VARS_LEGEND;
	global $CS_VARIABLE_ASSOCIATE_META_TITLE_VARS_LEGEND;

	//Get proper array for outputting legend information
	if( $prefix == "listings" ) {
		$legend = $CS_VARIABLE_LISTING_META_TITLE_VARS_LEGEND;
	} else if( $prefix == "communities" ) {
		$legend = $CS_VARIABLE_COMMUNITY_META_TITLE_VARS_LEGEND;
	} else if( $prefix == "associates" ) {
		$legend = $CS_VARIABLE_ASSOCIATE_META_TITLE_VARS_LEGEND;
	} else {
		$legend = array("n/a" => "n/a");
	}

	// If we're generating an options legend for the content section add the extra options if it's for an applicable page.
	if( $for_content_section ) {
		if( $prefix == "listings" ) {
			$legend['Listing Description'] = "%d";
		} else if( $prefix == "associates" ) {
			$legend['Associate Description'] = "%d";
		}
	}

	return $legend;
}

/**
 * Renders an formatting options array as a 2 col table.
 */
function render_format_options_table( $format_options, $indent = '' ) {

	echo '<table class="cs-seo-available-formats-table">';
	echo '  <tbody>';
	echo '    <tr>';

	$arr_counter = 0;
	foreach( $format_options as $key => $value) {
		$arr_counter++;

		echo ' <td>' . $key . ' : ' . $value . '</td>';

		if( ( $arr_counter % 2 ) == 0 ) {
			echo '</tr><tr>';
		}

	} 

	echo '    </tr>';
	echo '  <tbody>';
	echo '</table>';
	echo '';
}

/**
 * Renders a single form section for the given page.
 *
 * param - $cs_page_settings - the page settings record from the cs_posts table (agumented with extra info).
 * param - $is_update - true if we're reloading just this form after a settings update.
 */
function render_cs_page_settings_form( $cs_page_settings, $is_update ) {

	global $CS_VARIABLE_PREFIX_META_HEADERS;

	$title_options_legend = get_options_legend( $cs_page_settings['prefix'] ); // Get the availalbe options based on which page this is (For the title)
	$content_options_legend = get_options_legend( $cs_page_settings['prefix'], true /* for content */ );

	$tabindex=($cs_page_settings['available'])?0:-1;

?>
<?php if(!$is_update){ ?>	  
  <div id="update_div_<?php echo $cs_page_settings['prefix']; ?>" class="cs-semiopacity">
<?php } ?>	  
<?php if (!$cs_page_settings['available']) { ?>
    <div class="cs-disabled-overlay"><table><tr><td> PRODUCT UPGRADE IS NEEDED TO ACCESS THIS FEATURE <a href="http://www.clicksold.com/pricing/" target="_blank">details...</a>  </td></tr></table></div> 
<?php }?>

    <form id="header_options_<?php echo $cs_page_settings['prefix']; ?>" name="header_options_<?php echo $cs_page_settings['post_title']; ?>" method="post" action="<?php echo plugins_url( "cs_seo_settings.php", __FILE__ ); ?>" class="cs-form">
      <div class="cs-form-section">
        <fieldset>
          <div class="cs-form-section-title"><?php echo $CS_VARIABLE_PREFIX_META_HEADERS[$cs_page_settings['prefix']]; ?></div>
          <table class="form-table">
            <tr valign="top">
              <th scope="row"><label>Title</label></th>
              <td>Options:
<?php   render_format_options_table( $title_options_legend ); ?>
              </td>
              <td><input tabindex="<?php echo($tabindex); ?>" name="header_title"  type="text" value="<?php echo cs_encode_for_html( $cs_page_settings['header_title'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label>Meta Description - Max Characters</label></th>
              <td>Note: Max Characters - 200</td>
              <td><input tabindex="<?php echo($tabindex); ?>" name="header_desc_char_limit" type="text" value="<?php echo $cs_page_settings['header_desc_char_limit']; ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label>Meta Description</label></th>
              <td>Options:
<?php render_format_options_table( $content_options_legend ); ?>
              </td>
              <td><textarea tabindex="<?php echo($tabindex); ?>" name="header_desc" class="regular-text" cols=40 rows=4><?php echo cs_encode_for_html( $cs_page_settings['header_desc'] ); ?></textarea></td>
            </tr>
          </table>
        </fieldset>
        <div class="cs-form-submit-buttons-box">
          <input tabindex="<?php echo($tabindex); ?>" type="submit" name="Submit" class="cs-button" value="<?php esc_attr_e('Save Changes') ?>" />
          <input type="hidden" name="postid" value="<?php echo $cs_page_settings['ID']; ?>" />
          <input type="hidden" name="<?php echo $cs_page_settings['prefix'] ?>" />
        </div>
      </div>
<?php	if($is_update){ ?>
      <div class="cs-form-feedback"><?php _e('Settings saved.'); ?></div>
<?php 	} ?>
  
    </form>
<?php if(!$is_update){ ?>	  
  </div>
<?php } ?>	  
<?php
}


/**----------------------------------------------------------------------------------
 * Main
 */

/**
 * Grab the current settings of the cs generated pages from the db.
 */
global $wpdb;
$table_name = $wpdb->prefix . "cs_posts";

$header_settings_query = "SELECT " . $wpdb->posts . ".ID, " . $wpdb->posts . ".post_title, " . $table_name . ".prefix, "  . $table_name . ".available, " . $table_name . ".parameter, " . $table_name . ".header_title, 
			" . $table_name . ".header_desc, " . $table_name . ".header_desc_char_limit FROM " . $wpdb->posts . ", " . $table_name . " WHERE " . $wpdb->posts . ".post_type = \"page\" 
			AND " . $wpdb->posts . ".post_parent = 0 AND " . $wpdb->posts . ".ID = " . $table_name . ".postid ORDER BY " . $wpdb->posts . ".ID ASC";
	
$cs_pages_settings = $wpdb->get_results($header_settings_query, ARRAY_A);

/**
 * Process the post if we're processing a save request.
 */
$section_to_reload = ''; // Blank for now as we don't know which one we need to re-load quite yet. (if we are re-loading this will hold the page's prefix).

// Find which page they are updating.
foreach( $cs_pages_settings as & $cs_page_settings ) { // NOTE: the & here causes foreach to work give us a reference NEEDED cause we'll be updating the $cs_page_settings

	/* NOTE! Using 'prefix' field for identifying posts... If that changes or we decide to use a proper  *
	* convention for identifying (unique) pages in wp_cs_posts then use that instead                   */
	if(isset($_POST[ $cs_page_settings['prefix'] ])) {
	
		// Tell the rendering section (below) which section to re-load.
		$section_to_reload = $cs_page_settings['prefix'];

		// NOTE: we ammend the cs_page_settings in place so we don't have to re-load them from the db.
		$cs_page_settings['header_title'] = $_POST['header_title'];
		$cs_page_settings['header_desc'] = $_POST['header_desc'];
			
		//Validate the value set for the character limit
		if(is_numeric($_POST['header_desc_char_limit'])){
			//Force value to be 200 if over
			if((int)$_POST['header_desc_char_limit'] > 200){
				$cs_page_settings['header_desc_char_limit'] = '200';
			}else{
				$cs_page_settings['header_desc_char_limit'] = $_POST['header_desc_char_limit'];
			}
		}else{
			//Set the character limit to the previous value if the posted value is not a number
			$cs_page_settings['header_desc_char_limit'] = $cs_page_settings['header_desc_char_limit'];
		}

		//Update the record
		$update_vars = array('header_title' => $cs_page_settings['header_title'], 'header_desc' => $cs_page_settings['header_desc'], 'header_desc_char_limit' => $cs_page_settings['header_desc_char_limit']);
		$update_vars_format = array('%s', '%s', '%d');
		$where_vars = array('postid' => $_POST['postid']);
		$where_vars_format = array('%d');
	
		$wpdb->update($table_name, $update_vars, $where_vars, $update_vars_format, $where_vars_format);
	}
}
unset( $cs_page_settings ); // Best practice, unset the foreach var as it hangs around (in this case it was conflicting with the next foreach cause the second one does not go by reference).

/**
 * Render the form (for individual re-load) or forms for an initial load.
 */
foreach( $cs_pages_settings as $cs_page_settings ) {

	if( $section_to_reload == '' || $section_to_reload == $cs_page_settings['prefix'] ) {
		render_cs_page_settings_form( $cs_page_settings, ( $section_to_reload != '' ) /* is update? */ ); // If section to re-load is not blank then we're reloading.

		// Bind the form for each form rendered.
?>
		<script type="text/javascript">
			$(document).ready(function(){

				// Hide all of the "Updated" messages on a page for that nice visual queue
				// of which form has been updated.
				var clearUpdatedMessages = function() {

					$('.cs-form-feedback').each(function () {
						$(this).fadeOut();
					});
				};

				$("#header_options_<?php echo $cs_page_settings['prefix']; ?>").clickSoldUtils("csBindToForm", {
					"updateDivId" : "update_div_<?php echo $cs_page_settings['prefix']; ?>",
					"loadingDivId" : "null",
					"beforeSubmit" : clearUpdatedMessages,
					"plugin" : true
				});
			});	
		</script>
<?php

	}
}
unset( $cs_page_settings );

?>



