<?php
/*
* Container for reusable functions
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

/**
 * This function adds one plugin page by inserting it into the wp_posts table as well as optionally putting it
 * in any custom menus that are present.
 */
function cs_add_plugin_pages( $page, $prefix ){

	global $wpdb;
	global $cs_posts_table;
	
	$post_title = $page;
	$post_name = strtolower($page);
	$post_status = 'private';
	$table_name = $wpdb->prefix . $cs_posts_table;
	
	$my_post = array(
		'post_title' => $post_title,
		'post_name' => $post_name,
		'post_status' => $post_status,
		'comment_status' => 'closed',  //Disable comments for these generated pages
		'ping_status' => 'closed'
	);
	
	// Attempt to get the current post ID if it already exists
	$post_id = $wpdb->get_var("SELECT $wpdb->posts.ID FROM $wpdb->posts, $table_name WHERE $wpdb->posts.ID = $table_name.postid AND $table_name.prefix = '$prefix'");
	
	// Insert the page/post into the database
	if(!isset($post_id)) {
		$post_id = wp_insert_post( $my_post );
		
		$guid =  get_option('siteurl') ."/?page_id=$post_id";
		$post_type = 'page';
		$wpdb->query("UPDATE $wpdb->posts SET guid = '$guid', post_type = '$post_type' WHERE ID = $post_id");
		
		/** For each (fixed / customised) menu on the account add our stuff. **/
		if( get_option("cs_allow_manage_menus", 1) ) {
			cs_add_post_to_custom_menus( $post_id, $post_type );
		}
	}
	
	return $post_id;
}

/**
 * This function adds the given post_id to all of the custom menus on the install.
 */
function cs_add_post_to_custom_menus( $post_id, $post_type, $post_status = "publish" ) {

	global $wpdb;

	// Create arguments to be used when inserting the menu item.
	$add_menu_item_args = array(
		'menu-item-object-id' => $post_id,
		'menu-item-object' => $post_type,
		'menu-item-type' => 'post_type',
		'menu-item-status' => $post_status
	);

	// Now add a reference to the page for each menu item.
	foreach( wp_get_nav_menus() as $menu ) {

		$items = wp_get_nav_menu_items( $menu->term_id, array( 'post_status' => 'publish,private,draft' ) );
		// Continue if we got junk (aka an error) from the wp_get_nav_menu_items call.
		if ( ! is_array( $items ) )
			continue;

		// Refuse to add the menu item if it already exists for this page (prevent conflicts with the menu auto add feature).
		foreach ( $items as $item ) {
			if ( $post_id == $item->object_id )
				continue 2;
		}

		$menu_item_id = wp_update_nav_menu_item( $menu->term_id, 0, $add_menu_item_args );

		// Note: I can't figure out why this does not happen automatically when being ran from the wp-control script.
		//       This essentially just links the new menu item to the menu.
		$obj_id = $wpdb->get_var("SELECT $wpdb->term_relationships.object_id FROM $wpdb->term_relationships WHERE $wpdb->term_relationships.object_id = '$menu_item_id' AND $wpdb->term_relationships.term_taxonomy_id = '$menu->term_taxonomy_id'");
		if(!isset($obj_id)) $wpdb->query("INSERT INTO $wpdb->term_relationships VALUES ( $menu_item_id, ".$menu->term_taxonomy_id.", 0)");
	}
}


/**
 * This function creates the table to track pages/posts that were created by cs
 * @author hoangker
 */
function cs_add_cs_post( $post_id, $default_page, $prefix, $post_parameter, $title, $desc, $desc_limit, $avail = 0){

	global $wpdb;
	global $cs_posts_table;
	
	//Check if 
	$existing_post_id = $wpdb->get_var("SELECT postid FROM " . $wpdb->prefix . $cs_posts_table . " WHERE postid = '$post_id'");
	if(!isset($existing_post_id)) {
		$wpdb->insert($wpdb->prefix . $cs_posts_table, 
		array( 'postid' => $post_id, 'defaultpage' => $default_page, 'prefix' => $prefix, 'parameter' => $post_parameter, 'header_title' => $title, 'header_desc' => $desc, 'header_desc_char_limit' => $desc_limit, 'available' => $avail ), 
		array('%d','%s','%s','%s','%s','%s','%d','%d'));
	}
	//error_log("Inserted cs_post with id: $post_id");
}

/**
 * This function checks the plugin url's host ip to see if this is being hosted by 
 * ClickSold servers or elsewhere.
 */
function cs_is_hosted(){
	global $CS_VARIABLE_HOSTING_ID;
	global $CS_VARIABLE_HOSTING_ID2;
	$plugin_host = gethostbyname($_SERVER['SERVER_NAME']);

	// Special var in the config to make the plugin think that it's on a 3rd party wp host.
	if(defined("CS_FORCE_3RD_PARTYHOST") && CS_FORCE_3RD_PARTYHOST) {
		return false;
	}

	if(defined("CS_DEBUG") && CS_DEBUG) {
		return(
			($plugin_host == $CS_VARIABLE_HOSTING_ID) ||
			($plugin_host == $CS_VARIABLE_HOSTING_ID2) ||
			($plugin_host == "127.0.0.1")			// Dev envs are also considered to be "hosted".
		);
	} else {
		return(
			($plugin_host == $CS_VARIABLE_HOSTING_ID) ||
			($plugin_host == $CS_VARIABLE_HOSTING_ID2)
		);
	}
}

/**
 * Determines if this plugin is operating on a multisite or not
 */
function cs_is_multsite() {
	global $wpmu_version;
	if (function_exists('is_multisite'))
		if (is_multisite()) return true;
	if (!empty($wpmu_version)) return true;
	return false;
}

/**
 * Saves all the information about a given cs page.
 */
function cs_save_cs_post_state( $prefix ) {

	global $wpdb;
	global $cs_posts_table;

	// First we try to load and check if the prefix is even valid.
	$cs_post_record = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix . $cs_posts_table.' WHERE prefix = "' . $prefix .'"');

	if( $cs_post_record == null ) { // No page with this prefix exists so we can't do much.
		return;
	}

	// Load any current saved state (or initialize if not found)
	$cs_posts_state = get_option( "cs_posts_state", array() );
	
	// We want to overwrite any data stored for this prefix (should not happen as restore state should have removed the record and save state should only be ran once before a page is removed).
	$cs_posts_state[ $prefix ] = array();
	$cs_posts_state[ $prefix ]['seo'] = array();            // Init our sections. (This is done because they will be treated atomically, if a section is not present all of it's values will not be restored when calling the restore function).
	$cs_posts_state[ $prefix ]['page_settings'] = array();  //   this is also to prevent namespace conflicts for out sections.

	// Save the data from the cs_posts table (SEO settings).
	$cs_posts_state[ $prefix ]['seo']['header_title'] = $cs_post_record->header_title;
	$cs_posts_state[ $prefix ]['seo']['header_desc'] = $cs_post_record->header_desc;
	$cs_posts_state[ $prefix ]['seo']['header_desc_char_limit'] = $cs_post_record->header_desc_char_limit;

	// Save the info about the actual post that represents the page.
	$wp_page = $wpdb->get_row('SELECT post_title, post_status, post_name FROM '.$wpdb->posts.' WHERE ID = "' . $cs_post_record->postid . '"');

	$cs_posts_state[ $prefix ]['page_settings']['post_title'] = $wp_page->post_title;
	$cs_posts_state[ $prefix ]['page_settings']['post_status'] = $wp_page->post_status;
	$cs_posts_state[ $prefix ]['page_settings']['post_name'] = $wp_page->post_name;

	// Save back and we're done.
	update_option( "cs_posts_state", $cs_posts_state );
}

/**
 * Restores all the information about a given cs page.
 */
function cs_restore_cs_post_state( $prefix ) {

	global $wpdb;
	global $cs_posts_table;

	// First we try to load and check if the prefix is even valid.
	$cs_post_record = $wpdb->get_row('SELECT * FROM '.$wpdb->prefix . $cs_posts_table.' WHERE prefix = "' . $prefix .'"');

	if( $cs_post_record == null ) { // No page with this prefix exists so we can't do much.
		return;
	}

	// Load any current saved state.
	$cs_posts_state = get_option( "cs_posts_state", array() );
	
	// If we don't have any state for this page we can just quit right here.
	if( !isset( $cs_posts_state[ $prefix ] ) ) { return; }

	// Restore the SEO settings (if available).
	if( isset( $cs_posts_state[ $prefix ]['seo'] ) ) {

		$wpdb->query( 'UPDATE '.$wpdb->prefix . $cs_posts_table.' SET header_title = "'.$cs_posts_state[ $prefix ]['seo']['header_title'].'", header_desc = "'.$cs_posts_state[ $prefix ]['seo']['header_desc'].'", header_desc_char_limit = "'.$cs_posts_state[ $prefix ]['seo']['header_desc_char_limit'].'"  WHERE prefix = "' . $prefix .'"' );
	}

	// Restore the page settings (if available).
	if( isset( $cs_posts_state[ $prefix ]['page_settings'] ) ) {
		$wpdb->query('UPDATE '.$wpdb->posts.' SET post_title = "'.$cs_posts_state[ $prefix ]['page_settings']['post_title'].'", post_status = "'.$cs_posts_state[ $prefix ]['page_settings']['post_status'].'", post_name = "'.$cs_posts_state[ $prefix ]['page_settings']['post_name'].'" WHERE ID = "' . $cs_post_record->postid . '"');

		// For the post status we also have to update any associated menu items.
		foreach ($wpdb->get_results('SELECT meta_value, post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_menu_item_object_id" and meta_value = "'.$cs_post_record->postid.'"') as $menu_item) {

			$wpdb->query('UPDATE '.$wpdb->posts.' SET post_status = "'.$cs_posts_state[ $prefix ]['page_settings']['post_status'].'" WHERE ID = "' . $menu_item->post_id . '"');
		}
	}

	// Remove the saved state section and save back or remove depending on if there is anything left to save.
	unset( $cs_posts_state[ $prefix ] );

	if( empty( $cs_posts_state ) ) {
		delete_option( "cs_posts_state" );
	} else {
		update_option( "cs_posts_state", $cs_posts_state );
	}
}

/**
 * Encode any special chars in the string such that we can put them on
 * an html page as enteties and therefore not have them mess up if the page
 * is being served as ISO-8859-1 while (by default) wordpress is utf-8.
 *
 * NOTE: We dynamically determine the blog's charset because htmlentities
 *       assumes ISO-8859-1 on php < 5.4.0 and UTF-8 on php >= 5.4.0.
 */
function cs_encode_for_html( $str ) {
	// Note: for this call the flags used are the defaults.
	if(defined('ENT_HTML401')) {  // ENT_HTML401 not available on php < 5.4.0
		return htmlentities( $str, ENT_COMPAT | ENT_HTML401, get_option( 'blog_charset', 'UTF-8' ) );
	} else {
		return htmlentities( $str, ENT_COMPAT, get_option( 'blog_charset', 'UTF-8' ) );
	}
}

/**
 * Wrapper around WP_Query.queried_object_id - some plugins get confused if we call get_queried_object_id so we query the instance var directly, however as of
 * WP 3.5 (I think) we're getting non defined warnings when WP_Debug is on. So we test to see if the var is defined and only use it if it is.
 * 
 * Reminder the incompatible plugin was WP-Property
 */
function cs_get_queried_object_id( $wp_query ) {

	if(! isset( $wp_query->queried_object_id ) ) {
		//$wp_query->get_queried_object_id(); // This is as of WP 3.5 throwing warnings regarding the $post object....
		return;
	} else {
		return $wp_query->queried_object_id;
	}
}

/**
 * Generates a string of key information about the wordpress install and ClickSold plugin state. Used to debug 3rd party wordpress hosting issues.
 */
function cs_generate_degbug_info() {
	
	global $wpdb;
	global $cs_posts_table; // Name of the cs_posts table.
	
	$output = '';
	
	// Add the date.
	$output = 'WP Reported Date: ' . date( 'Y-m-d H:i:s e O' ) . "<br>\n"; // Eg. 2013-02-05 16:25:34 UTC +0000
	
	$output .= "<br>\n-------------------- wordpress info --------------------<br>\n";
	$output .= "Name: '" . get_bloginfo('name') . "'<br>\n";
	$output .= "Desc/Tagline: '" . get_bloginfo('description') . "'<br>\n";
	$output .= "WP Url: '" . get_bloginfo('wpurl') . "'<br>\n";
	$output .= "Site URL: '" . get_bloginfo('siteurl') . "'<br>\n";
	$output .= "Version: '" . get_bloginfo('version') . "'<br>\n";
	$output .= "Template Url: '" . get_bloginfo('template_url') . "'<br>\n";
	if( is_multisite() ) {
		$output .= "Is Multisite: 'Yes'<br>\n";
	} else {
		$output .= "Is Multisite: 'No'<br>\n";
	}
	
	// CS Posts Table.
	$output .= "<br>\n-------------------- cs_posts --------------------<br>\n";
	$output .= cs_generate_html_table_from_db_result_set( $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . $cs_posts_table . "", ARRAY_A ) );
	
	// Options table - CS options.
	$output .= "<br>\n-------------------- options (cs*) --------------------<br>\n";
	$output .= cs_generate_html_table_from_db_result_set( $wpdb->get_results("SELECT * FROM " . $wpdb->options . " where option_name like 'cs%' ", ARRAY_A ) );
	
	// Plugins.
	$output .= "<br>\n-------------------- Plugins --------------------<br>\n";
	$output .= cs_print_r_html( cs_get_plugins_enchanced( true ), true);

	// WP Rewrites
	$output .= "<br>\n-------------------- WP Rewrites --------------------<br>\n";
	$output .= "*** WARNING: This only includes the default rules with wp_rewrite->rules = cs_add_rewrite_rules... this is NOT a valid representation of the rewrite rules as they appear on the front end.<br>\n";
	global $wp_rewrite; // NOTE: our rewrite rules are added using the rewrite_rules_array filter and that's not called in the admin area so we have to apply our chances as IF this is what we were working with.
	$wp_rewrite->rules = cs_add_rewrite_rules( array() );
	$output .= cs_print_r_html( $wp_rewrite, true);
	
	// WP Filter - Global
	$output .= "<br>\n-------------------- wp_filter ('the_content' - filter on the content itself) --------------------<br>\n";
	global $wp_filter;
	$output .= cs_print_r_html( $wp_filter['the_content'], true);

	// Options table - CS options (Goes last as it's usually stupid long).
	$output .= "<br>\n-------------------- posts --------------------<br>\n";
	$output .= cs_generate_html_table_from_db_result_set( $wpdb->get_results("SELECT * FROM " . $wpdb->posts . "", ARRAY_A ) );

	return cs_kill_script( $output );
} 

/**
 * Returns a formatted html table given a mysql result array (as returned by $wpdb->get_results(ARRAY_A output type)). Used for presentation of table data.
 */
function cs_generate_html_table_from_db_result_set( $result ) {
	
	// Edge case for when the result is empty.
	if( count( $result ) == 0 ) {
		return "<table><tr><th>Empty Result.</th></tr></table>";
	}
	
	// Now we know that we have at least one result.
	
	$output = '';
	$output .= "<table>\n";

	// Figure out the field names.
	$field_names = array_keys( $result[0] );

	// Print the header.
	$output .= "  <tr>\n";
	for( $i = 0; $i < count( $field_names ); $i++ ) {
		$output .= "    <th>" . $field_names[$i] . "</th>\n";
	}
	$output .= "  </tr>\n";
	
	// Now the rows.
	for( $row = 0; $row < count( $result ); $row++ ) {
		
		$output .= "  <tr>\n";
		
		for( $col = 0; $col < count( $field_names ); $col++ ) {
			
			$output .= "    <td>" . $result[ $row ][ $field_names[ $col ] ] . "</td>\n";

		}
		
		$output .= "  </tr>\n";
	}
	
	$output .= "</table>\n";
	return $output;
}

/**
 * Wraps around the print_r routine but makes the output html friendly this works by just surrounding the output with a <pre> (preformatted) tag.
 */
function cs_print_r_html( $obj, $return_string ) {
	
	$print_r_output = print_r( $obj, true );
	
	if( $return_string ) { // Return as a string.
		return "<pre>".$print_r_output."</pre>";
	} else { // Just print it just like print_r( $obj, false ) would do.
		print "<pre>".$print_r_output."</pre>";
	}
	return "";
}

/**
 * Starting with the output of get_plugins() this routine then adds the info as to if the plugins are activated or network activated.
 */
function cs_get_plugins_enchanced( $strip_useless_details ) {
	
	$plugins = get_plugins();
	
	// For each plugin test if it's activated or not and if it's netwrok activated or not.
	foreach( $plugins as $plugin_path => $plugin_details ) {
		
		// Add the values that we need to know about.
		if( is_plugin_active( $plugin_path ) ) {
			$plugins[ $plugin_path ]['Is Active'] = 'Yes';
		} else {
			$plugins[ $plugin_path ]['Is Active'] = 'No';
		}
		
		if( is_plugin_active_for_network( $plugin_path ) ) {
			$plugins[ $plugin_path ]['Is Network Active'] = 'Yes';
		} else {
			$plugins[ $plugin_path ]['Is Network Active'] = 'No';
		}

		// get_plugins returns a lot of info about each plugin, some of it is useless for our needs so if requested we remove it.
		if( $strip_useless_details ) {
			unset( $plugins[ $plugin_path ]['PluginURI'] );
			unset( $plugins[ $plugin_path ]['Author'] );
			unset( $plugins[ $plugin_path ]['AuthorURI'] );
			unset( $plugins[ $plugin_path ]['TextDomain'] );
			unset( $plugins[ $plugin_path ]['DomainPath'] );
			unset( $plugins[ $plugin_path ]['Network'] );
			unset( $plugins[ $plugin_path ]['Title'] );
			unset( $plugins[ $plugin_path ]['AuthorName'] );
		}
	}
	
	return $plugins;
}

/**
 * Kills all of the script tags in the source by replacing "<script" / "</script" with "<cs_killed_script" and "</cs_killed_script" respectively.
 * Used for the debug send generation so the scripts on pages don't get executed in the Plugin Activation tab (where the debug output is stored).
 */
function cs_kill_script( $input ) {
	
	$input = str_replace( "<script ", "<cs_killed_script ", $input );
	$input = str_replace( "</script ", "</cs_killed_script ", $input );
	$input = str_replace( "<iframe ", "<cs_killed_iframe ", $input );
	$input = str_replace( "</iframe ", "</cs_killed_iframe ", $input );
	
	return $input;
}

/**
 * Returns yes or no if the currently logged in user can administer the ClickSold plugin.
 * 
 * As this is here to guard the cs admin functions this will obviously return false if the user is not logged in.
 */
function cs_current_user_can_admin_cs() {
	
		/** WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING WARNING 
			The wp_login hook cs_wp_login re-implemnets this as this function does not work yet before the user is fully logged in.
			* That one should be updated as well if this implementation is changed. **/
	
	// Until we go to the trouble of adding a specific cs-admin role... cs admins are defined as those who can manage options.
	// NOTE: 2013-03-27 the CS admin area menu is linked to the manage_options capability as is the updating of the plugin number and key.
	return current_user_can('manage_options');
}

/**
 * Returns the value of the $cs_plugin_type global or 'unknown' if it's not defined.
 */
function cs_get_cs_plugin_type() {
	global $cs_plugin_type;

	if( !isset( $cs_plugin_type ) ) { return 'Unknown'; }

	return $cs_plugin_type;
}

/**
 * Just a defined function that does nothing that we can put in actions if we need a placeholder.
 */
function cs_null_function() {
	// Does nothing.
}

?>
