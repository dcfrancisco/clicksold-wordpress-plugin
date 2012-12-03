<?php 
/*
* Form used to update the listings, idx, and community permalinks as well as show or hide those pages.  Used in the ClickSold - Website Settings
* under the Page Settings tab.
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

// Note: These have to be included before any output is sent to the browser so that servers with buffering disabled don't break when we try to start our session.
require_once('../../../wp-load.php');
require_once('cs_constants.php');

?>
<div id="ws_page_settings" class="cs-module">
  <?php 
	global $wpdb;
	global $wp_rewrite;
	global $CS_SECTION_PARAM_CONSTANTS;
	
	$listings_title_error = "";
	$listings_url_error = "";
	$idx_title_error = "";
	$idx_url_error = "";
	$communities_title_error = "";
	$communities_url_error = "";
	$associates_title_error = "";
	$associates_url_error = "";
	$duplicate_post_names = array();
		
	$idx_opts_reload = false;
	$comm_opts_reload = false;
		
	//Get all the post ids of our generated pages
	$wp_cs_posts = $wpdb->get_results("SELECT prefix, postid FROM " . $wpdb->prefix . "cs_posts", OBJECT_K);
		
	$cs_brokerage = get_option("cs_opt_brokerage");
	
	// Other plugin options.
	$cs_allow_manage_menus = get_option("cs_allow_manage_menus", 1);
	$cs_delay_shortcode_insert = get_option("cs_delayed_shortcodes", 0);
	$cs_opt_use_cookies_instead_of_sessions = get_option("cs_opt_use_cookies_instead_of_sessions", 0);

	$valid = true;
	
	$cs_db_data = $wpdb->get_results('SELECT prefix, available FROM ' . $wpdb->prefix . 'cs_posts WHERE PREFIX IN("'. implode('", "', $CS_SECTION_PARAM_CONSTANTS) .' ")', ARRAY_A);
	$cs_available = array();
	foreach($cs_db_data as $index){
		$cs_available[$index['prefix']] = $index['available'];
	}
	$cs_tabindex = $cs_available;
	function get_tabindex(&$val,$key){$val=($val)?0:-1;};
	array_walk($cs_tabindex,"get_tabindex");
	
	//Do basic validation on post names and titles - check for empty strings
	if( isset( $_POST["post_success"] ) ) { // If we're doing a post.
		$post_names = array();
		
		// We check diff fields depending on what they submitted (which in turn depends on what's available on the page).
		if( isset( $_POST["listings_post_name"] ) ) {
			if( empty( $_POST["listings_post_name"] ) || empty( $_POST["listings_post_title"] ) ) $valid = false;
			else {
				$old_post_name = $wpdb->get_var("SELECT post_name FROM " . $wpdb->prefix . "posts WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["listings_pname"] ]->postid);
				if( $_POST["listings_post_name"] != $old_post_name ) $post_names["listings_post_name"] = $_POST["listings_post_name"];
			}
		}
		
		if( isset( $_POST["communities_post_name"] ) ) {
			if( empty( $_POST["communities_post_name"] ) || empty( $_POST["communities_post_title"] ) ) $valid = false;
			else {
				$old_post_name = $wpdb->get_var("SELECT post_name FROM " . $wpdb->prefix . "posts WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["community_pname"] ]->postid);
				if( $_POST["communities_post_name"] != $old_post_name ) $post_names["communities_post_name"] = $_POST["communities_post_name"];
			}
		} else $comm_opts_reload = true;
		
		if( isset( $_POST["idx_post_name"] ) ) {
			if( empty( $_POST["idx_post_name"] ) || empty( $_POST["idx_post_title"] ) ) $valid = false;
			else {
				$old_post_name = $wpdb->get_var("SELECT post_name FROM " . $wpdb->prefix . "posts WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["idx_pname"] ]->postid);
				if( $_POST["idx_post_name"] != $old_post_name ) $post_names["idx_post_name"] = $_POST["idx_post_name"];
			}
		} else $idx_opts_reload = true;
		
		if( isset( $_POST["associates_post_name"] ) ) {
			if( empty( $_POST["associates_post_name"] ) || empty( $_POST["associates_post_title"] ) ) $valid = false;
			else {
				$old_post_name = $wpdb->get_var("SELECT post_name FROM " . $wpdb->prefix . "posts WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["assoc_pname"] ]->postid);
				if( $_POST["associates_post_name"] != $old_post_name ) $post_names["associates_post_name"] = $_POST["associates_post_name"];
			}
		}
					
		// Check if modified urls already exist in WP
		if( count($post_names) > 0 ) {
			$duplicate_post_names = $wpdb->get_col('SELECT post_name FROM ' . $wpdb->prefix . 'posts where post_name IN ("' . implode('", "', $post_names) . '") GROUP BY post_name HAVING COUNT(post_name) > 0');
			if( count( $duplicate_post_names ) > 0 ) $valid = false;
		}
	}

	if( isset($_POST["post_success"]) ) {

		// This form works, updates and respects the post_status field of the actual posts but we must save the desired statuses also.
		$cs_posts_desired_statuses = get_option( "cs_posts_desired_statuses", array('listings' => 'publish', 'idx' => 'publish', 'communities' => 'publish', 'associates' => 'publish' ) ); // By default all are desired to be shown.

		//Get post name values from post request
		$listings_post_name = $_POST["listings_post_name"];
		$idx_post_name = $_POST["idx_post_name"];
		$communities_post_name = $_POST["communities_post_name"];
		
		$listings_post_title = $_POST["listings_post_title"];
		$idx_post_title = $_POST["idx_post_title"];
		$communities_post_title = $_POST["communities_post_title"];
		
		if($cs_brokerage){
			$associates_post_name = $_POST["associates_post_name"];
			$associates_post_title = $_POST["associates_post_title"];
		}

		//Get front page as it may need to be reset
		$front_page = get_option('page_on_front');
		$reset_front_page = false;
		
		//Set checkbox values - note that pages set as password protected in the WordPress pages section will have
		//empty checkboxes
		if( isset($_POST["listings_post_name"]) ) {
			if(isset($_POST["listings_post_status"]) && $_POST["listings_post_status"] == "1"){
				$listings_post_status = "publish";
			}else{
				$listings_post_status = "private";
				if($front_page == $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["listings_pname"] ]->postid) $reset_front_page = true;
			}

			$cs_posts_desired_statuses['listings'] = $listings_post_status;
		}

		if( isset($_POST["idx_post_name"]) ) {
			if(isset($_POST["idx_post_status"]) && $_POST["idx_post_status"] == "1"){
				$idx_post_status = "publish"; 
			}else{
				$idx_post_status = "private"; 
				if($front_page == $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["idx_pname"] ]->postid) $reset_front_page = true;
			}

			$cs_posts_desired_statuses['idx'] = $idx_post_status;
		}

		if( isset($_POST["communities_post_name"]) ) {
			if(isset($_POST["communities_post_status"]) && $_POST["communities_post_status"] == "1"){
				$communities_post_status = "publish"; 
			}else{
				$communities_post_status = "private"; 
				if($front_page == $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["community_pname"] ]->postid) $reset_front_page = true;
			}

			$cs_posts_desired_statuses['communities'] = $communities_post_status;
		}

		//Brokerage page - associates
		if($cs_brokerage){
			if( isset($_POST["associates_post_name"]) ) {
				if(isset($_POST["associates_post_status"]) && $_POST["associates_post_status"] == "1"){
					$associates_post_status = "publish";
				}else{
					$associates_post_status = "private"; 
					if($front_page == $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["assoc_pname"] ]->postid) $reset_front_page = true;
				}

				$cs_posts_desired_statuses['associates'] = $associates_post_status;
			}
		}
		
		if( $valid == true ) {
			
			// Save the desired statuses for all of the posts. (This is so when the posts are hidden or shown in response to available features we know what the desired values were).
			update_option( "cs_posts_desired_statuses", $cs_posts_desired_statuses );

			//read children menu items  
			foreach ($wpdb->get_results('SELECT meta_value, post_id FROM '.$wpdb->postmeta.' WHERE meta_key = "_menu_item_object_id"') as $value)
				$nav_menu_items[$value->meta_value][] = $value->post_id;

			//Update values (But only if they were actually submitted)
			if( isset( $_POST["listings_post_name"] ) ) {
				$wpdb->update($wpdb->posts, array("post_title" => $listings_post_title, "post_name" => $listings_post_name, "post_status" => $listings_post_status), array("ID" => $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["listings_pname"] ]->postid), array("%s", "%s", "%s"), array("%d"));
				$nav_menu_item_ids = $nav_menu_items[$wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["listings_pname"] ]->postid];
				if (isset($nav_menu_item_ids))
					foreach ($nav_menu_item_ids as $id)
						$wpdb->update($wpdb->posts, array("post_status" => $listings_post_status), array("ID" => $id), array("%s"), array("%d"));
			}
			
			if( isset( $_POST["idx_post_name"] ) ) {
				$wpdb->update($wpdb->posts, array("post_title" => $idx_post_title, "post_name" => $idx_post_name, "post_status" => $idx_post_status), array("ID" => $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["idx_pname"] ]->postid), array("%s", "%s", "%s"), array("%d"));
				$nav_menu_item_ids = $nav_menu_items[$wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["idx_pname"] ]->postid];
				if (isset($nav_menu_item_ids))
					foreach ($nav_menu_item_ids as $id)
						$wpdb->update($wpdb->posts, array("post_status" => $idx_post_status), array("ID" => $id), array("%s"), array("%d"));
			}

			if( isset( $_POST["communities_post_name"] ) ) {
				$wpdb->update($wpdb->posts, array("post_title" => $communities_post_title, "post_name" => $communities_post_name, "post_status" => $communities_post_status), array("ID" => $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["community_pname"] ]->postid), array("%s", "%s", "%s"), array("%d"));
				$nav_menu_item_ids = $nav_menu_items[$wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["community_pname"] ]->postid];
				if (isset($nav_menu_item_ids))
					foreach ($nav_menu_item_ids as $id)
						$wpdb->update($wpdb->posts, array("post_status" => $communities_post_status), array("ID" => $id), array("%s"), array("%d"));
			}

			if( isset( $_POST["cs_allow_manage_menus"] ) ) {
				update_option("cs_allow_manage_menus", 1);
				$cs_allow_manage_menus = get_option("cs_allow_manage_menus");
			} else {
				update_option("cs_allow_manage_menus", 0);
				$cs_allow_manage_menus = get_option("cs_allow_manage_menus");
			}

			if( isset( $_POST["cs_delay_shortcode_insert"] ) ) {
				update_option("cs_delayed_shortcodes", 1);
				$cs_delay_shortcode_insert = get_option("cs_delayed_shortcodes");
			} else {
				update_option("cs_delayed_shortcodes", 0);
				$cs_delay_shortcode_insert = get_option("cs_delayed_shortcodes");
			}
			
			if( isset( $_POST["cs_opt_use_cookies_instead_of_sessions"] ) ) {
				update_option("cs_opt_use_cookies_instead_of_sessions", 1);
				$cs_opt_use_cookies_instead_of_sessions = get_option("cs_opt_use_cookies_instead_of_sessions");
			} else {
				update_option("cs_opt_use_cookies_instead_of_sessions", 0);
				$cs_opt_use_cookies_instead_of_sessions = get_option("cs_opt_use_cookies_instead_of_sessions");
			}

			//Site front page has been set to private so it has to be set back to posts
			if($reset_front_page === true){
				update_option('page_on_front', '0');
				update_option('show_on_front', 'posts');
			}
			
			if($cs_brokerage && isset( $_POST["associates_post_name"] ) ) {
				$wpdb->update($wpdb->posts, array("post_title" => $associates_post_title, "post_name" => $associates_post_name, "post_status" => $associates_post_status), array("ID" => $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["assoc_pname"] ]->postid), array("%s", "%s", "%s"), array("%d"));
				$nav_menu_item_ids = $nav_menu_items[$wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["assoc_pname"] ]->postid];
				if (isset($nav_menu_item_ids))
					foreach ($nav_menu_item_ids as $id)
						$wpdb->update($wpdb->posts, array("post_status" => $associates_post_status), array("ID" => $id), array("%s"), array("%d"));
			}
			
			//We need to flush the rules after updating the post names or they will never work
			$wp_rewrite->flush_rules();

		//Show updated message
?>
<!--  <div class="updated">
    <p><strong><?php _e('Settings saved.'); ?></strong></p>
  </div>-->
  <?php 
		} else {
			
			$errorMsg = "Please check the following errors below:<br/>";
			$errorMsg .= "<ul>";

			if(isset( $_POST["listings_post_title"] ) && empty($_POST["listings_post_title"])){
				$errorMsg .= "<li>Listings title is invalid</li>";
				$listings_title_error = "error";
			}
			
			if(isset( $_POST["listings_post_name"] )){
				if(empty($_POST["listings_post_name"])){
					$errorMsg .= "<li>Listings URL is invalid</li>";
					$listings_url_error = "error";
				}else if(array_key_exists("listings_post_name", $post_names) && in_array($_POST["listings_post_name"], $duplicate_post_names)) {
					$errorMsg .= "<li>Listings URL is already in use</li>";
					$listings_url_error = "error";
				}
			}

			if(isset( $_POST["idx_post_title"] ) && empty($_POST["idx_post_title"])){
				$errorMsg .= "<li>IDX title is invalid</li>";
				$idx_title_error = "error";
			}
			
			if(isset( $_POST["idx_post_name"] )){ 
				if(empty($_POST["idx_post_name"])){
					$errorMsg .= "<li>IDX URL is invalid</li>";
					$idx_url_error = "error";
				}else if(array_key_exists("idx_post_name", $post_names) && in_array($_POST["idx_post_name"], $duplicate_post_names)){
					$errorMsg .= "<li>IDX URL is already in use</li>";
					$idx_url_error = "error";
				}
			}

			if(isset( $_POST["communities_post_title"] ) && empty($_POST["communities_post_title"])){
				$errorMsg .= "<li>Communities title is invalid</li>";
				$communities_title_error = "error";
			}
			
			if(isset( $_POST["communities_post_name"] )){ 
				if(empty($_POST["communities_post_name"])){
					$errorMsg .= "<li>Communities URL is invalid</li>";
					$communities_url_error = "error";
				}else if(array_key_exists("communities_post_name", $post_names) && in_array($_POST["communities_post_name"], $duplicate_post_names)){
					$errorMsg .= "<li>Communities URL is already in use</li>";
					$communities_url_error = "error";
				}
			}
			
			if(isset( $_POST["associates_post_title"] ) && $cs_brokerage && empty($_POST["associates_post_title"])){
				$errorMsg .= "<li>Associates title is invalid</li>";
				$associates_title_error = "error";
			}
			
			if(isset( $_POST["associates_post_name"] ) && $cs_brokerage){
				if(empty($_POST["associates_post_name"])){
					$errorMsg .= "<li>Associates URL is invalid</li>";
					$associates_url_error = "error";
				}else if(array_key_exists("associates_post_name", $post_names) && in_array($_POST["associates_post_name"], $duplicate_post_names)){
					$errorMsg .= "<li>Associates URL is already in use</li>";
					$associates_url_error = "error";
				}
			}
						
			$errorMsg .= "</ul>";
?>
<!--  <div class="error">
    <p><strong><?php _e($errorMsg); ?></strong></p>
  </div>-->
<?php
		}
	}
	
	if(!isset($_POST["post_success"]) || $comm_opts_reload == true || $idx_opts_reload == true){
	
		//Retrieve values from wp_posts table
		if(!isset($_POST["post_success"]) || $idx_opts_reload == true) { 
			$idx_post = $wpdb->get_results("SELECT post_title, post_name, post_status FROM " . $wpdb->posts . " WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["idx_pname"] ]->postid);
			$idx_post_title = $idx_post[0]->post_title;
			$idx_post_name = $idx_post[0]->post_name;
			$idx_post_status = $idx_post[0]->post_status;
		}
		
		if(!isset($_POST["post_success"]) || $comm_opts_reload == true) { 
			$communities_post = $wpdb->get_results("SELECT post_title, post_name, post_status FROM " . $wpdb->posts . " WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["community_pname"] ]->postid);
			$communities_post_title = $communities_post[0]->post_title;
			$communities_post_name = $communities_post[0]->post_name;
			$communities_post_status = $communities_post[0]->post_status;
		}
		
		if(!isset($_POST["post_success"])) { 
			$listings_post = $wpdb->get_results("SELECT post_title, post_name, post_status FROM " . $wpdb->posts . " WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["listings_pname"] ]->postid);
			$listings_post_title = $listings_post[0]->post_title;
			$listings_post_name = $listings_post[0]->post_name;
			$listings_post_status = $listings_post[0]->post_status;
			
			if($cs_brokerage){
				$associates_post = $wpdb->get_results("SELECT post_title, post_name, post_status FROM " . $wpdb->posts . " WHERE ID = " . $wp_cs_posts[ $CS_SECTION_PARAM_CONSTANTS["assoc_pname"] ]->postid);
				$associates_post_title = $associates_post[0]->post_title;
				$associates_post_name = $associates_post[0]->post_name;
				$associates_post_status = $associates_post[0]->post_status;
			}
		}
	}
?>
  <form id="ws_page_settings_form" name="ws_page_settings" method="post" action="<?php echo plugins_url( "cs_page_settings.php", __FILE__ ); ?>" class="cs-form cs-form-inline">
    <div class="cs-semiopacity">
<?php if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["listings_pname"]]){ ?> 
      <div class="cs-disabled-overlay"><table><tr><td> PRODUCT UPGRADE IS NEEDED TO ACCESS THIS FEATURE <a href="http://www.clicksold.com/pricing/" target="_blank">details...</a>  </td></tr></table></div> 
<?php }  ?>
      <div id="ws_page_settings_form_listings" class="cs-form-section">
        <fieldset>
          <div class="cs-form-section-title">Listings Page</div>
          <div class="cs-label-container"><label for="listings_post_title">Title in Menu:</label></div><div class="cs-input-container"><div class="cs-adjust-for-box-model"><input tabindex="<?php echo($cs_tabindex["listings"]); ?>" type="text" class="<?php echo( cs_encode_for_html( $listings_title_error ) ); ?>" name="listings_post_title" value="<?php echo cs_encode_for_html( $listings_post_title ); ?>"/></div></div><div class="cs-input-small"><div class="cs-label-container"><label for="listings_post_status">Show Page:<span class="cs-required-field">*</span>:</label></div><input tabindex="<?php echo($cs_tabindex["listings"]); ?>" type="checkbox" name="listings_post_status" value="1" <?php if($listings_post_status == "publish"){ ?>checked="checked"<?php } ?> class="cs-checkbox"/></div><div class="cs-label-container"><label for="listings_post_name" style="text-align:right;">http://www.mydomain.com/</label></div><div class="cs-input-container cs-input-long"><div class="cs-adjust-for-box-model"><input tabindex="<?php echo($cs_tabindex["listings"]); ?>" type="text" class="<?php echo( cs_encode_for_html( $listings_url_error ) ); ?>" name="listings_post_name" value="<?php echo cs_encode_for_html( $listings_post_name ); ?>"/></div></div>
        </fieldset>  
      </div> 
    </div>
    
    <div class="cs-semiopacity">
<?php if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["idx_pname"]]){ ?> 
      <div class="cs-disabled-overlay"><table><tr><td> PRODUCT UPGRADE IS NEEDED TO ACCESS THIS FEATURE <a href="http://www.clicksold.com/pricing/" target="_blank">details...</a>  </td></tr></table></div> 
<?php }  ?>
      <div id="ws_page_settings_form_idx" class="cs-form-section">
        <fieldset class="cs-page-settings">
          <div class="cs-form-section-title">MLS<sup>&reg;</sup> Search Page</div>
          <div class="cs-label-container"><label for="idx_post_title">Title in Menu:</label></div><div class="cs-input-container"><div class="cs-adjust-for-box-model"><input tabindex="<?php echo($cs_tabindex["idx"]); ?>" type="text" class="<?php echo( cs_encode_for_html( $idx_title_error ) ); ?>" name="idx_post_title" value="<?php echo cs_encode_for_html( $idx_post_title ); ?>"/></div></div><div class="cs-input-small"><div class="cs-label-container"><label for="idx_post_status">Show Page:<span class="cs-required-field">*</span>:</label></div><input tabindex="<?php echo($cs_tabindex["idx"]); ?>" type="checkbox" name="idx_post_status" value="1" <?php if($idx_post_status == "publish"){ ?>checked="checked"<?php } ?> class="cs-checkbox"/></div><div class="cs-label-container"><label for="idx_post_name" style="text-align:right;">http://www.mydomain.com/</label></div><div class="cs-input-container cs-input-long"><div class="cs-adjust-for-box-model"><input tabindex="<?php echo($cs_tabindex["idx"]); ?>" type="text" class="<?php echo( cs_encode_for_html( $idx_url_error ) ); ?>" name="idx_post_name" value="<?php echo cs_encode_for_html( $idx_post_name ); ?>"/></div></div>
        </fieldset>
      </div>
    </div>
    <div class="cs-semiopacity">
<?php if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["community_pname"]]){ ?> 
      <div class="cs-disabled-overlay"><table><tr><td> PRODUCT UPGRADE IS NEEDED TO ACCESS THIS FEATURE <a href="http://www.clicksold.com/pricing/" target="_blank">details...</a>  </td></tr></table></div> 
<?php }  ?>
      <div id="ws_page_settings_form_comm" class="cs-form-section">
        <fieldset class="cs-page-settings">
          <div class="cs-form-section-title">Communities Page</div>
          <div class="cs-label-container"><label for="communities_post_title">Title in Menu:</label></div><div class="cs-input-container"><div class="cs-adjust-for-box-model"><input tabindex="<?php echo($cs_tabindex["communities"]); ?>" type="text" class="<?php echo( cs_encode_for_html( $communities_title_error ) ); ?>" name="communities_post_title" value="<?php echo cs_encode_for_html( $communities_post_title ); ?>"/></div></div><div class="cs-input-small"><div class="cs-label-container"><label for="communities_post_status">Show Page:<span class="cs-required-field">*</span>:</label></div><input tabindex="<?php echo($cs_tabindex["communities"]); ?>" type="checkbox" name="communities_post_status" value="1" <?php if($communities_post_status == "publish"){ ?>checked="checked"<?php } ?> class="cs-checkbox"/></div><div class="cs-label-container"><label for="communities_post_name" style="text-align:right;">http://www.mydomain.com/</label></div><div class="cs-input-container cs-input-long"><div class="cs-adjust-for-box-model"><input tabindex="<?php echo($cs_tabindex["communities"]); ?>" type="text" class="<?php echo( cs_encode_for_html( $communities_url_error ) ); ?>" name="communities_post_name" value="<?php echo cs_encode_for_html( $communities_post_name ); ?>"/></div></div>
        </fieldset>
      </div>
    </div>
<?php if($cs_brokerage){ ?>
    <div class="cs-semiopacity">
<?php if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["assoc_pname"]]){ ?> 
      <div class="cs-disabled-overlay"><table><tr><td> PRODUCT UPGRADE IS NEEDED TO ACCESS THIS FEATURE <a href="http://www.clicksold.com/pricing/" target="_blank">details...</a>  </td></tr></table></div> 
<?php }  ?>
      <div id="ws_page_settings_form_assoc" class="cs-form-section">
        <fieldset class="cs-page-settings">
          <div calss="cs-disabled-overlay"><div>
          <div class="cs-form-section-title">Associates Page</div>
          <div class="cs-label-container"><label for="associates_post_title">Title in Menu:</label></div><div class="cs-input-container"><div class="cs-adjust-for-box-model"><input tabindex="-2" type="text" class="<?php echo( cs_encode_for_html( $associates_title_error ) ); ?>" name="associates_post_title" value="<?php echo cs_encode_for_html( $associates_post_title ); ?>"/></div></div><div class="cs-input-small"><div class="cs-label-container"><label for="associates_post_status">Show Page:<span class="cs-required-field">*</span>:</label></div><input  tabindex="-2" type="checkbox" name="associates_post_status" value="1" <?php if($associates_post_status == "publish"){ ?>checked="checked"<?php } ?> class="cs-checkbox"/></div><div class="cs-label-container"><label for="associates_post_name" style="text-align:right;">http://www.mydomain.com/</label></div><div class="cs-input-container cs-input-long"><div class="cs-adjust-for-box-model"><input  tabindex="-2" type="text" class="<?php echo( cs_encode_for_html( $associates_url_error ) ); ?>" name="associates_post_name" value="<?php echo cs_encode_for_html( $associates_post_name ); ?>"/></div></div>
        </fieldset>
      </div>
    </div>
<?php }  ?>	
    <div class="cs-semiopacity">
      <div id="ws_page_settings_form_manage_menus" class="cs-form-section">
        <fieldset class="cs-page-settings">
          <div class="cs-input-small">
            <div class="cs-label-container">
              <label for="cs_allow_manage_menus">Allow ClickSold Plugin to update menus:<span class="cs-required-field">*</span>:</label>
            </div>
            <input type="checkbox" name="cs_allow_manage_menus" value="1" <?php if($cs_allow_manage_menus){ ?>checked="checked"<?php } ?> class="cs-checkbox"/>
          </div>
          <div class="cs-input-small">
            <div class="cs-label-container">
              <label for="cs_delay_shortcode_insert">Delay CS ShortCode Insertion:<span class="cs-required-field">*</span>:</label>
            </div>
            <input type="checkbox" name="cs_delay_shortcode_insert" value="1" <?php if($cs_delay_shortcode_insert){ ?>checked="checked"<?php } ?> class="cs-checkbox"/>
          </div>
          <div class="cs-input-small">
            <div class="cs-label-container">
              <label for="cs_opt_use_cookies_instead_of_sessions">Use Cookies instead of PHP Sessions:<span class="cs-required-field">*</span>:</label>
            </div>
            <input type="checkbox" name="cs_opt_use_cookies_instead_of_sessions" value="1" <?php if($cs_opt_use_cookies_instead_of_sessions){ ?>checked="checked"<?php } ?> class="cs-checkbox"/>
          </div>
        </fieldset>
      </div>
    </div>

    <div class="cs-form-submit-buttons-box">
      <input type="hidden" id="post_success" name="post_success" value="y" />
      <input type="submit" class="cs-button" value="Save" />
    </div>
<?php 	if(isset($_POST["post_success"])){ 
		if($valid){?>
    <div class="cs-form-feedback">
<?php 		_e('Settings saved.'); ?>    
    </div>
<?php 		}else{  ?>
    <div class="cs-form-feedback cs-form-feedback-error">
<?php 		_e($errorMsg); ?>
    </div>
<?php 		}
	}?>
  </form>
</div>
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$("#ws_page_settings_form").clickSoldUtils("csBindToForm", {
			"updateDivId" : "ws_page_settings",
			"loadingDivId" : "null",
			"plugin" : true
		});
<?php if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["listings_pname"]]){ ?> 
		$("input", "#ws_page_settings_form_listings").each(function(){
			$(this).prop("disabled", "disabled");
		});
		
		$("select", "#ws_page_settings_form_listings").each(function(){
			$(this).prop("disabled", "disabled");
		});
<?php } if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["idx_pname"]]){ ?>
		$("input", "#ws_page_settings_form_idx").each(function(){
			$(this).prop("disabled", "disabled");
		});
		
		$("select", "#ws_page_settings_form_idx").each(function(){
			$(this).prop("disabled", "disabled");
		});
<?php } if(!$cs_available[$CS_SECTION_PARAM_CONSTANTS["community_pname"]]){ ?> 
		$("input", "#ws_page_settings_form_comm").each(function(){
			$(this).prop("disabled", "disabled");
		});
		
		$("select", "#ws_page_settings_form_comm").each(function(){
			$(this).prop("disabled", "disabled");
		});
<?php } if($cs_brokerage && !$cs_available[$CS_SECTION_PARAM_CONSTANTS["assoc_pname"]]){?>
		$("input", "#ws_page_settings_form_assoc").each(function(){
			$(this).prop("disabled", "disabled");
		});
		
		$("select", "#ws_page_settings_form_assoc").each(function(){
			$(this).prop("disabled", "disabled");
		});
<?php } ?>
	});
})(csJQ);
</script>
