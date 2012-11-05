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

	$post_title = $page;
	$post_name = strtolower($page);
	$post_status = 'private';
	
	$my_post = array(
		'post_title' => $post_title,
		'post_name' => $post_name,
		'post_status' => $post_status,
		'comment_status' => 'closed',  //Disable comments for these generated pages
		'ping_status' => 'closed'
	);
	
	// Insert the page/post into the database
	$post_id = wp_insert_post( $my_post );
	
	$guid =  get_option('siteurl') ."/?page_id=$post_id";
	$post_type    = 'page';
	$wpdb->query("UPDATE $wpdb->posts SET guid = '$guid', post_type = '$post_type' WHERE ID = $post_id");
	
	/** For each (fixed / customised) menu on the account add our stuff. **/
	if( get_option("cs_allow_manage_menus", 1) ) {
		cs_add_post_to_custom_menus( $post_id, $post_type );
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
		$wpdb->query("INSERT INTO $wpdb->term_relationships VALUES ( $menu_item_id, ".$menu->term_taxonomy_id.", 0)");
	}
}


/**
 * This function creates the table to track pages/posts that were created by cs
 * @author hoangker
 */
function cs_add_cs_post( $post_id, $default_page, $prefix, $post_parameter, $title, $desc, $desc_limit, $avail = 0){

	global $wpdb;
	global $cs_posts_table;
	$wpdb->insert($wpdb->prefix . $cs_posts_table, 
	array( 'postid' => $post_id, 'defaultpage' => $default_page, 'prefix' => $prefix, 'parameter' => $post_parameter, 'header_title' => $title, 'header_desc' => $desc, 'header_desc_char_limit' => $desc_limit, 'available' => $avail ), 
	array('%d','%s','%s','%s','%s','%s','%d','%d'));
	
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

?>
