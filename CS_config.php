<?php
/*
* Contains routines used for activating/deactivating this plugin's configurations
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
require_once('cs_functions.php');

	class CS_config{
		
		function CS_config(){}
	
		/** Plugin Activation Routines **********************************************************************/
		
		function cs_activate(){

			global $wpdb;
			global $wp_rewrite;
			global $cs_posts_table;
			global $cs_change_products_request;
			
			// create cs tables
			$this->cs_install();
			
			// Plugin pages include:
			// 1. listings,
			// 2. neighbourhoods
			// 3. idx
			
			$listing_page = array( "Title" => "Listings",               // the page title for this page
						"DefaultPage" => "featured_listings.html",	// default landing page if no parameters are specified
						"Prefix" => "listings",               // the string we add to the mlsnum parameter so the plugin server knows this is a listing request
						"Parameters" => array("mlsnum"),
						"Header_Title" => "%a | ",
						"Header_Desc" => "%d", 
						"Header_Desc_Char_Limit" => 200);  // parameters that the Listings page takes  

			$neighbourhood_page = array( "Title" => "Communities",		// the page title for this page 	
						"DefaultPage" => "community.html",	// default landing page if no parameters are specified 
						"Prefix" => "communities",		// the string we add to the mlsnum parameter so the plugin server knows this is a neighbourhood request
						"Parameters" => array("community"),
						"Header_Title" => "%c | %n |",
						"Header_Desc" => "Search results for %n", 
						"Header_Desc_Char_Limit" => 200);    // parameters that the Listings page takes

			$idx_page = array( "Title" => "MLS&reg; Search",   // the page title for this page
						"DefaultPage" => "search.html",  // default landing page if no parameters are specified
						"Prefix" => "idx",          // the string we add to the mlsnum parameter so the plugin server knows this is a listing request
						"Parameters" => array("junk"),
						"Header_Title" => "MLS Search", // Keep the &reg; out of this as it does not save / load from the db correctly.
						"Header_Desc" => "MLS Search", 
						"Header_Desc_Char_Limit" => 200);

				 
			// create ClickSold wp pages
			$pages = array( $listing_page, $neighbourhood_page, $idx_page );
			
			foreach($pages as $page){
				$post_id = cs_add_plugin_pages( $page["Title"], $page["Prefix"] );
				
				if(count($page["Parameters"]) > 1){
					//Comma separate the individual parameters
					$parameter = implode( ",", $page["Parameters"] );
				}else{
					$parameter = $page["Parameters"][0];
				}
				
				// store the post_id, default page, and the parameter associated with that post
				$default_page = $page["DefaultPage"];
				$prefix = $page["Prefix"];
				$header_title = $page["Header_Title"];
				$header_desc = $page["Header_Desc"];
				$header_desc_char_limit = $page["Header_Desc_Char_Limit"];
				
				cs_add_cs_post($post_id, $default_page, $prefix, $parameter, $header_title, $header_desc, $header_desc_char_limit);

				// Restore any saved settings for this page.
				cs_restore_cs_post_state( $prefix );

				// Additionally for the idx page, set the layout to full page IF they are running a genesis theme.
				if( $prefix == 'idx' ) {
					update_post_meta( $post_id, '_genesis_layout', 'full-width-content' );
				}
			}
			
			// add cs options to wp_options table
			$this->cs_add_cs_options();
			$this->cs_plugin_toggle_brokerage(get_option("cs_opt_brokerage"));

			// Regenerate the rewrite rules and save them to the database
			$wp_rewrite->flush_rules();

			// Change the main page to the previously deactivated account's page (if available)
			// NOTE: option 'cs_front_page' contains the CS page's prefix as it should never change
			$cs_front_page = get_option('cs_front_page');
			if( $cs_front_page !== false ){
				//set front page
				$front_page_id = $wpdb->get_var( 'SELECT postid FROM ' . $wpdb->prefix . $cs_posts_table . ' WHERE prefix = "' . $cs_front_page . '"' );
				if( !is_null( $front_page_id ) ) {
					update_option('page_on_front', $front_page_id );
					update_option('show_on_front', 'page');
				}
				//remove option
				delete_option('cs_front_page');
			}
			
			// Load page settings from previously deactivated plugin (if available)

			// Request a re-synch from the cs server to see which ones (if any) of these freshly added pages have to be
			// hidden because they are not allowed by the current tier.
			update_option($cs_change_products_request, "1"); // Make it think that we changed the product, this requests a re-synchronization with the cs server.
		}

		/**
		 * This function creates ClickSold db tables in wordpress owners wp db.
		 * Thought of using wp_options to store posts created by cs plugin
		 * but could not because its not an appropriate 
		 * type of data you'd expect the site owner to enter when first setting up the plugin, and rarely change thereafter.
		 * Also thought of using Post Meta add_post_meta() but we could not retrieve the data without a post_id
		 * or key. Creating our own table was most logical.
		 */
		function cs_install(){
			global $wpdb;
			global $cs_db_version;
			global $cs_posts_table;
			$table_name = $wpdb->prefix . $cs_posts_table;
			
			$installed_ver = get_option( "cs_db_version" );
			
			if($installed_ver != $cs_db_version){

				// WARNING: if the tables don't seem to reflect the script
				// then ensure that $installed_ver != $cs_db_version
				$sql = "DROP TABLE IF EXISTS " . $table_name . ";";
				$wpdb->query($sql);
				
				//postid:      the id of the post returned by wp
				//defaultpage: the landing page for this particular post
				//prefix:      the string we add to the beginning of parameter
				//parameter:   one of potentially many parameters associated with this post
				$sql = "CREATE TABLE " . $table_name . " (
					  postid mediumint(9) NOT NULL AUTO_INCREMENT, 
					  defaultpage VARCHAR(100) NOT NULL,
					  prefix  VARCHAR(50) NOT NULL,
					  parameter VARCHAR(25) NOT NULL,
					  header_title VARCHAR(66) NOT NULL DEFAULT '',
					  header_desc VARCHAR(200) NOT NULL DEFAULT '',
					  header_desc_char_limit SMALLINT NOT NULL DEFAULT 200,
					  available TINYINT NOT NULL DEFAULT 0,
					  PRIMARY  KEY  (postid, parameter)
					);";

				$wpdb->query($sql);
				
				update_option("cs_db_version", $cs_db_version);
			}
		}

		/** Plugin Deactivation Routines **********************************************************************/
		
		//on plugin deactivation do some cleanup
		function cs_deactivate(){
			global $wp_rewrite;
			$this->cs_remove_plugin_pages();
			$this->cs_remove_cs_posts();
			remove_filter('rewrite_rules_array', 'cs_add_rewrite_rules');
			$wp_rewrite->flush_rules();
			update_option("cs_change_products_request",1);
		}

		/**
		 * This function deletes the plugin pages
		 * @author hoangker
		 */
		function cs_remove_plugin_pages(){

			// Get post_id associated with plugin posts then
			// loop through and delete all the posts. Use: wp_delete_post()
			global $wpdb;
			global $cs_posts_table;
			$table_name = $wpdb->prefix . $cs_posts_table;
			$posts = $wpdb->get_results("SELECT postid, prefix, header_title, header_desc, header_desc_char_limit FROM $table_name");
			$front_page = get_option('page_on_front');
			
			// loop through and delete the ClickSold generated pages. NOTE: we leave all subpages created by the user if they exist.
			// If we also want to delete subpages under the CS generated pages change the where clause to include "OR post_parent = $a->postid"
			foreach ($posts as $a) {
				// Save prefix in 'cs_front_page' if it is currently set as the front page
				if($front_page == $a->postid) { 
					update_option('cs_front_page', $a->prefix);
					update_option('page_on_front', '0');  //Reset as the page is about to be deleted
					update_option('show_on_front', 'posts');
				}
				
				// Save the page state for when the plugin gets re-activated.
				cs_save_cs_post_state( $a->prefix );
				
				// So that it deletes the menu items as well, not not deleting associated stuff is useless for now
				// as we can't get it back anyways because the post id's change when CS re-adds them.
				wp_delete_post ($a->postid, true /* force, aka no trash */ );

				// For some reason wp_delete_post does not catch these.
				$wpdb->query("DELETE FROM ".$wpdb->term_relationships." where object_id = '".$a->postid."'");
			}
		}

		/**
		 * This function creates the table to track pages/posts that were created by ClickSold
		 * @author hoangker
		 */
		function cs_remove_cs_posts(){

			global $wpdb;
			global $cs_posts_table;
			$table_name = $wpdb->prefix . $cs_posts_table;
			$wpdb->query("TRUNCATE TABLE $table_name");
			//This will reset the next auto increment value to current largest value in the auto increment column + 1, ie. 1
			//since we just deleted all the records.
			$wpdb->query("ALTER TABLE $table_name AUTO_INCREMENT = 1");
			
		}
		
		/** Plugin Configuration Routines **********************************************************************/
		
		/**
		* DEPRECATED - Routine to compare the brokerage flag on the server with the one on the WP schema
		*/
		/*
		function cs_plugin_check_brokerage($page_vars){
			global $cs_opt_brokerage;
			global $CS_VARIABLE_CONFIG_ACCOUNTTYPE;
			
			if( array_key_exists( $CS_VARIABLE_CONFIG_ACCOUNTTYPE, $page_vars ) ){
				$opt_brokerage_val = get_option( $cs_opt_brokerage );
				$cs_brokerage_val = 0;
				
				if( $page_vars[ $CS_VARIABLE_CONFIG_ACCOUNTTYPE ] === "true" ){ $cs_brokerage_val = 1; }
				
				if( $opt_brokerage_val != $cs_brokerage_val ){ $this->cs_plugin_toggle_brokerage($cs_brokerage_val); }
			}
		}
		*/
		
		function cs_plugin_check_brokerage($var){
			global $cs_opt_brokerage;
			
			$opt_brokerage_val = get_option( $cs_opt_brokerage );
			$cs_brokerage_val = 0;
			
			if( $var === "true" ){ $cs_brokerage_val = 1; }
			if( $opt_brokerage_val != $cs_brokerage_val ){ $this->cs_plugin_toggle_brokerage($cs_brokerage_val); }
		}

		/**
		 * Function used to modify the existing pages for use with the brokerage product
		 *   - Modifies listing pathway
		 *   - Modifies agents view (or add/remove?)
		 *   - Adds/Removes agent details (single associate view with listing search)
		 *   - Sets the new brokerage flag value (WP schema)
		 */
		private function cs_plugin_toggle_brokerage($setBrokerage){
			global $wpdb;
			global $wp_rewrite;
			global $cs_opt_brokerage;
			
			$cs_posts_table = $wpdb->prefix . "cs_posts";
			if($setBrokerage == 1){ //Create "Associates" page & cs_posts entry
				$post_id = $wpdb->get_var( "SELECT postid FROM $cs_posts_table WHERE prefix = 'associates'" );
				
				if(is_numeric($post_id) && $post_id == 0) return false; //Error
				
				if(is_null($post_id)){
					//Create "Associates" page
					$assoc_page = array(	"Title" => "Associates",
								"DefaultPage" => "associates.html",
								"Prefix" => "associates",
								"Parameters" => array("associates"),
								"Header_Title" => "Profile: %f %l",
								"Header_Desc" => "%d",
								"Header_Desc_Char_Limit" => 200);
					
					//Get Page id
					$post_id = cs_add_plugin_pages( $assoc_page["Title"], "associates" );
					
					if($post_id == 0) return false;
					
					// create associates cs_page entry (with default values for now).
					cs_add_cs_post($post_id, $assoc_page["DefaultPage"], $assoc_page["Prefix"], $assoc_page["Parameters"][0], $assoc_page["Header_Title"], $assoc_page["Header_Desc"], $assoc_page["Header_Desc_Char_Limit"], 1);

					// Restore any saved settings for this page.
					cs_restore_cs_post_state( $assoc_page["Prefix"] );

				}else return false;
				
			}else{ //Remove "Associates" page & cs_page entry
				//Get page id from wp_cs_pages
				$assoc_page = $wpdb->get_row( "SELECT postid, header_title, header_desc, header_desc_char_limit FROM $cs_posts_table WHERE prefix = 'associates'" );
				//$post_id
				if(isset($assoc_page->postid)){

					// Save any state for the associates page.
					cs_save_cs_post_state( 'associates' );
				
					//delete child pages
					foreach ($wpdb->get_results('SELECT post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_menu_item_object_id" AND meta_value = '.$assoc_page->postid, OBJECT_K) as $child_post_id => $value)
						wp_delete_post ($child_post_id);
										
					//delete wp_pages entry
					wp_delete_post ($assoc_page->postid, true /* force, aka no trash */ );
					
					//delete wp_cs_pages entry
					$wpdb->query( $wpdb->prepare("DELETE FROM $cs_posts_table WHERE postid = %d", $assoc_page->postid) );
					
					$wpdb->query("DELETE FROM ".$wpdb->term_relationships." where object_id = '".$assoc_page->postid."'");
				}
			}
			
			$wp_rewrite->flush_rules();
			update_option( $cs_opt_brokerage, $setBrokerage );
			return true;
		}
		
		/**
		 * This function adds the ClickSold options into the wp_options table for later use
		 * when we form the http requests
		 */
		private function cs_add_cs_options(){
			global $cs_plugin_options;
			foreach($cs_plugin_options as $key => $value){
				add_option( $key, $value );
			}
			update_option("cs_change_products_request", 1);
		}
		
		/**
		 * Function for processing ClickSold variables piggybacked onto the header of a page request.  Currently sets values to 
		 * options that are already available
		 */
		public function cs_add_request_vars($page_vars) {
			global $blog_id;
			$ms = is_multisite();
			
			foreach($page_vars as $key => $value) {
				if(!get_option( $key, FALSE ) == FALSE) {
					if($ms == true) {
						update_blog_option( $blog_id, $key, $value );
					} else {
						update_option( $key, $value);
					}
				}
			}
		}
	}
?>
