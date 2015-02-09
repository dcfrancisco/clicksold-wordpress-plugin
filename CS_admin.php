<?php
/*
* Class used for plugin administration views / processing
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
global $cs_logo_path;
global $cs_offers_config;
global $cs_help_page;

require_once(plugin_dir_path(__FILE__) . 'CS_config.php');
require_once(plugin_dir_path(__FILE__) . 'cs_functions.php');
require_once(plugin_dir_path(__FILE__) . 'widgets.php');

$cs_posts_table = "cs_posts";
$cs_logo_path = plugins_url("images/orbGreen.png", __FILE__);
$cs_help_page = "";

	class CS_admin{
		
		private $response;
		
		/** Initialization Routines ***********************************************************/
		
		/**
		 * Main constructor function run on object instantiation - sets up menu items and additional hooks for specific page scripts & styles
		 */
		function CS_admin(){
			global $CS_ADMIN_MENU_ITEMS;
			
			// setup menu items
			add_action('init', array($this, 'get_admin_section'));
			
			// Add TinyMCE scripts & styles to the My Listings view
			if( array_key_exists('page', $_GET ) && ( $CS_ADMIN_MENU_ITEMS['My Listings']['menu_slug'] == $_GET['page'] || $CS_ADMIN_MENU_ITEMS['My Website']['menu_slug'] == $_GET['page'] ) ) {
				add_action('admin_head', array($this, 'init_editor'));
			} else {
				// Add CS Shortcodes button to all editors except in the listings section
				$cs_shortcodes = new CS_shortcodes( 'cs_listings' );
				add_action('init', array( $cs_shortcodes, 'cs_add_tinymce_buttons' ) );
			}
			
			// build plugin admin menu item
			add_action('admin_menu', array($this, 'cs_admin_menu'));
			
			add_action('parse_query', array($this, 'hide_unavailable_cs_pages'));
			
			// add a dashboard widget for cs.
			add_action('wp_dashboard_setup', array($this, 'cs_add_custom_dashboard_widget'));
			
			// Prevent CS pages from being deleted
			add_action("wp_trash_post", array($this, "prevent_cs_trash_pages"), 1, 12);
			add_action("wp_delete_post", array($this, "prevent_cs_trash_pages"), 1, 12);
			
			// Update page status when page is updated
			add_action("edit_post", array($this, 'cs_edit_page_status'));
			
			if(strpos($_SERVER["QUERY_STRING"], "cs_page_del_error=true")) add_action('admin_notices', array($this, 'display_cs_page_delete_error'));
			
			// CS Notifications
			add_action('admin_notices', array($this, 'display_cs_notices'));
			
			// CS Login Popup 
			add_action('admin_init', array($this, 'cs_show_offers_popup_config'));
		}
		
		/**
		 * 2015-01-02 EZ - this appears to be incomplete!
		 */
		function cs_show_offers_popup_config(){
			global $cs_offers_config;
			global $CS_SECTION_ADMIN_PARAM_CONSTANT;
			
			$showOffers = get_option("cs_opt_show_offers_popup", "0");
			if($showOffers == "1") {
				update_option("cs_opt_show_offers_popup", "0");
				
				$cs_request = new CS_request( "pathway=633", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"] );
				$cs_response = new CS_response( $cs_request->request() );
				if($cs_response->is_error()) return;
				
				$cs_offers_config = $cs_response->cs_get_json();
				
				if(!empty($cs_offers_config)) {
					add_action('admin_enqueue_scripts', array($cs_response, 'cs_get_header_contents_linked_only'), 0);
					add_action('admin_print_scripts', array($this, 'cs_show_offers_popup'), 20);
				}
			}
		}
		
		/**
		 * 2015-01-02 EZ - this appears to be incomplete!
		 */
		function cs_show_offers_popup() {
			global $cs_offers_config;
			if(empty($cs_offers_config)) return;
			
			$imgStyles = "";
			if(!empty($cs_offers_config['imgWidth'])) $imgStyles .= "width:{$cs_offers_config['imgWidth']};";
			if(!empty($cs_offers_config['imgHeight'])) $imgStyles .= "height:{$cs_offers_config['imgHeight']};"; 
			if(!empty($imgStyles)) $imgStyles = " styles=\\\"$imgStyles\\\"";
			
			$imgUrl = $cs_offers_config['imgUrl'];
			if(empty($imgUrl)) $imgUrl = plugins_url('images/welcome-upgrade.png', __FILE__);
			
			$nonce = wp_create_nonce("cs_disable_offers_popup");
			$utilsUrl = plugins_url('CS_ajax_utilities.php', __FILE__);
			
			echo
				"<script>
					(function($){ 
						$(document).ready(function(){				 
							var csPopupHtml = \"<div id=\\\"cs_offers_popup\\\">\" +
							                  \"  <div class=\\\"cs_offers_popup_image\\\">\" +
											  \"    <a href=\\\"http://{$cs_offers_config['link']}\\\" target=\\\"_blank\\\">\" +
											  \"      <img src=\\\"$imgUrl\\\"$imgStyles>\" +
											  \"    </a>\" +
											  \"  </div>\" +
											  \"  <div class=\\\"cs_disable_popup_form\\\">\" +
											  \"    <label>Do not show this again</label>\" + 
											  \"    <input id=\\\"cs_disable_offers_popup\\\" type=\\\"checkbox\\\" name=\\\"disable_popup\\\" />\" +
											  \"  </div>\" +
											  \"</div>\";
									  
							var csPopupOpts = {
								html : csPopupHtml,
								className : \"cs_offers_popup_modal\",
								width : \"{$cs_offers_config['modWidth']}\",
								height : \"{$cs_offers_config['modHeight']}\",
								onCleanup : function(){
									if($(\"#cs_disable_offers_popup\").is(\":checked\")) {
										$.ajax({
											type:\"GET\",
											url: \"$utilsUrl\",
											data: \"disableOffersPopup=true&_csnonce=$nonce\"
										});
									}
								}
							};
							
							$.clickSoldUtils(\"infoBoxCreate\", csPopupOpts);
						});
					})(csJQ);
				 </script>";
		}
		
		/**
		* Changes the saved status (visibility) value when a page is updated
		*/
		function cs_edit_page_status($page_id) {
			global $wpdb;
			global $cs_posts_table;
			
			$prefix = $wpdb->get_var('SELECT prefix FROM '. $wpdb->prefix . $cs_posts_table . " WHERE postid = $page_id");
			if(isset($prefix)) {
				$status = $wpdb->get_var("SELECT post_status FROM $wpdb->posts WHERE ID = $page_id");
				$cs_posts_desired_statuses = get_option( "cs_posts_desired_statuses", array('listings' => 'publish', 'idx' => 'publish', 'communities' => 'publish', 'associates' => 'publish' ) );
				$cs_posts_desired_statuses[$prefix] = $status;
				update_option("cs_posts_desired_statuses", $cs_posts_desired_statuses);
			}
		}
		
		/**
		 * Initializes the TinyMCE Editor for use with the plugin
		 */
		function init_editor(){
			global $tinymce_version;
			if(version_compare($tinymce_version, '4021', 'lt') && function_exists('wp_tiny_mce')) wp_tiny_mce(false);
		}
		
		/**
		 * Queries the server to notify the user of any account misconfigurations 
		 */
		function display_cs_notices() {
			global $CS_SECTION_ADMIN_PARAM_CONSTANT;
			
			if(get_option("cs_opt_notify", "1") == "1") {
				$cs_request = new CS_request( "pathway=623", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"] );
				$cs_response = new CS_response( $cs_request->request() );
				if($cs_response->is_error()) return;
				$notices = $cs_response->get_body_contents();
				update_option("cs_opt_notify_msgs", $notices);
				update_option("cs_opt_notify", "0");
			} else $notices = get_option("cs_opt_notify_msgs", "");
			
			$notices = trim($notices);
			if(!empty($notices)) echo "<div class=\"updated\">" . $notices . "</div>";
		}
		
		/**
		 * Splices the menu items array const based on whether or not this is a brokerage product
		 */
		function get_menu_items(){
			global $CS_ADMIN_MENU_ITEMS;
			global $cs_opt_brokerage;
			global $current_blog;
			global $cs_opt_tier_name;

			$menu = $CS_ADMIN_MENU_ITEMS;
			
			if(is_multisite()){
				$isBrok = get_blog_option($current_blog->blog_id, "cs_opt_brokerage");
			}else{
				$isBrok = get_option("cs_opt_brokerage");
			}
			
			foreach($menu as $key => $item){
				//look for the brokerage field - process if it is set
				if(array_key_exists("brokerage", $item)){
					if($item['brokerage'] != $isBrok){
						//Remove the item
						unset($menu[$key]);
					}
				}

				// If we are not hosted remove the My Domains (aka the domain manager) menu item.
				if( !cs_is_hosted() ) {
					if( $item['menu_slug'] == 'cs_plugin_admin_domains' ) {
						//Remove the item
						unset($menu[$key]);
					}
					
					// Also e-mail can't be managed if we are not hosted.
					if( $item['menu_slug'] == 'cs_plugin_admin_email' ) {
						//Remove the item
						unset($menu[$key]);
					}
				}

				// Supress the "Upgrade!" menu item if we are platinum.
				if( $item['menu_slug'] == "cs_plugin_product_config_direct" && get_option( $cs_opt_tier_name, 'Bronze' ) == "Platinum" ) {
					//Remove the item
					unset($menu[$key]);
				}
			}
			
			return $menu;
		}
		
		/**
		 * Initializes a prompt to delete the demo listing from this account.
		 */
		function cs_prompt_demo_listing_delete() {
			echo 
				'<script>
					(function($){
						$(document).ready(function() {
							$.clickSoldUtils("csPromptDeleteDemoListing", { "ajaxTarget" : "' . plugins_url( 'CS_ajax_request.php', __FILE__ ) . '?wp_admin_pname=wp_admin&section=wp_admin" });
						});
					})(csJQ);
				</script>';
		}
		
		/**
		 * Adds a ClickSold help link to the admin bar
		 */
		function cs_add_help_link() {
			global $wp_admin_bar;
			global $cs_help_page;
			
			if( !is_super_admin() || !is_admin_bar_showing() ) return;
			
			$wp_admin_bar->add_node(
				array (
					'id' => 'cs_help_link',
					'title' => __('ClickSold Help'),
					'href' => __('http://www.clicksold.com/wiki/index.php/' . $cs_help_page),
					'meta' => array ('target' => '_blank')
				)
			);
		}
		
		/**
		 * Checks page slug (if available) and retrieves data from ClickSold server if necessary
		 */
		function get_admin_section(){
		
			global $CS_VARIABLE_META_ADMIN_NOTIFICATIONS;
			global $CS_VARIABLE_META_ADMIN_DELETE_DEMO_LISTING;
			global $CS_VARIABLE_ADMIN_HELP_PAGE;
			global $cs_help_page;
			
			$menu = $this->get_menu_items();
			$this->response = null;
			
			if(empty($menu)) return; //This will occur when the user is in the network admin section (WPMS)
			
			if(!empty($_GET['page'])){
				$slug = $_GET['page'];
				//Check if the slug is one of ours
				if(strpos($slug, "cs_plugin_") == 0){
					foreach($menu as $item){
						if((array_key_exists('server', $item) && $item['server'] == true) && $item['menu_slug'] == $slug){
							$org_req = $item['request'];
							//Check for any extra query string params (other than the page param) and add to the
							//request
							if(count($_GET) > 1){
								$prepend = "?";
								foreach($_GET as $param_name => $param_value){
									if($param_name != "page"){
										$org_req .= $prepend . $param_name . "=" . $param_value;
										$prepend = "&";
									}
								}
							}
							
							$cs_request = new CS_request($org_req, "wp_admin");
							$this->response = new CS_response($cs_request->request());
							
							if($this->response->is_error()) {
								wp_redirect(get_option('siteurl') . '/wp-admin/index.php');
								exit;
							}
							
							$page_vars = $this->response->cs_set_vars();
							if(!empty($page_vars)) {
								// Admin Notifications
								if(array_key_exists($CS_VARIABLE_META_ADMIN_NOTIFICATIONS, $page_vars)) update_option("cs_opt_notify", "1");
								
								// Remove demo listing after upgrade
								if(array_key_exists($CS_VARIABLE_META_ADMIN_DELETE_DEMO_LISTING, $page_vars)) add_action("admin_footer", array($this, 'cs_prompt_demo_listing_delete'));
								
								// Set the help link based on the selected page
								if(array_key_exists($CS_VARIABLE_ADMIN_HELP_PAGE, $page_vars)) $cs_help_page = $page_vars[$CS_VARIABLE_ADMIN_HELP_PAGE];
							}
							
							/* DEPRECATED
							//Get page var for plugin config and configure as necessary
							$cs_config = new CS_config();
							$cs_config->cs_plugin_check_brokerage($page_vars);
							*/
							
							//Add actions for setting header/footer resources
							add_action('admin_bar_menu', array($this, 'cs_add_help_link'), 1000);
							add_action('admin_enqueue_scripts', array($this->response, 'cs_get_header_contents_linked_only'), 0);
							add_action('admin_enqueue_scripts', array($this->response, 'cs_get_header_contents_inline_only'), 11);
							add_action('admin_print_footer_scripts', array($this->response, 'cs_get_footer_contents'));
							break;
						}
					}
				}
			}
		}
		
		/**
		 * Sets up the administration menu items for this plugin
		 */
		function cs_admin_menu() {
			global $menu;
			$csMenu = $this->get_menu_items();
			global $cs_logo_path;
			
			if(empty($csMenu)) return; //This will occur when the user is in the network admin section (WPMS)
			
			// Check if plugin is valid
			$cs_request = new CS_request("pathway=620", "wp_admin");
			$cs_response = new CS_response($cs_request->request());
			$valid = json_decode($cs_response->get_body_contents());
			
			//Register plugin admin menu items			
			foreach($csMenu as $name => $config){
				if($config['level'] == 'top'){
					
					add_menu_page($config['page_title'], $name, 'manage_options', $config['menu_slug'], array($this, $config['callback']), $cs_logo_path, '4.1');
					$menu['4.15'] = array( '', 'manage_options', '', '', 'wp-menu-separator' );
				} else if ( !is_null( $valid ) ) {  // Show the submenu if the plugin is valid

					if( isset($config['name']) ) { $name = $config['name']; }

					// 2013-02-28 EZ - Instead of filtering out the domain manager menu item here based on the info returned by the plugin server we will do so in get_menu_items based on the result of the cs_is_hosted() function... which is more reliable as it really tells us if the plugin is running remotely not just what we *think* is going on.
					//if($config['request'] == 'domain_manager' && $valid->acct_info == "none") continue;
					
					if( !isset( $config['external_link'] ) || "" == $config['external_link'] ) { // Regular internal link.

						add_submenu_page($config['parent_slug'], $config['page_title'], $name, 'manage_options', $config['menu_slug'], array($this, $config['callback']));
					} else { // External submenu link.

						// We have to add it directly to the admin menu because there is no function to add arbitrary external links.
						global $submenu; // Submenu here is a misnomer as it's actually the full menu.
						global $cs_help_page;
						$ext_link = $config['external_link'];
						if(!empty($cs_help_page)) $ext_link = str_replace('Main_Page', $cs_help_page, $ext_link);
						
						if( isset( $submenu[ $config['parent_slug'] ] ) ) { // This won't be present for non admin users.
							array_push( $submenu[ $config['parent_slug'] ], array( $name, 'manage_options' , $ext_link ) );
						}
					}
				}
			}
/*
			// testing code. uncomment to see the $menu contents
			add_action('admin_init','dump_admin_menu');
			function dump_admin_menu() {
			  if (is_admin()) {
			    header('Content-Type:text/plain');
			    var_dump($GLOBALS['menu']);
			    exit;
			  }
			}
*/
		}
				
		/**
		 * Add the ClickSold Dashboard widget.
		 */
		function cs_add_custom_dashboard_widget() {

			// First of all enqueue our css as we'll need that.
			$a_widget = new CS_Widget();		// Dashboard widget css is part of the package of all css for widgets so it's included by doing this call.
			$a_widget->get_widget_scripts( true );

			// Add the ClickSold Dashboard widget (this get's added to the "core" dashboard).
			wp_add_dashboard_widget('cs_custom_dashboard_widget', 'ClickSold Dashboard', array($this, 'cs_custom_dashboard_widget'), array($this, 'cs_custom_dashboard_widget_control_callback'));
	
			// We only force the widget to the top left if we're trying to show the help links.
			$show_help_links = 1; // The default.
			$widget_options = get_option( 'dashboard_widget_options' );
			if( isset( $widget_options['cs_custom_dashboard_widget']['show_help_links'] ) ) {
				$show_help_links = $widget_options['cs_custom_dashboard_widget']['show_help_links'];
			}

			// If the user has configured it such that they don't want the help links we don't bother shoving the dashboard widget to the top.
			if( !$show_help_links ) {
				return;
			}

			global $wp_meta_boxes;

			$core_dashboard = $wp_meta_boxes['dashboard']['normal']['core']; // Get the "core" wp-admin dashboard, normal priority.

			// Grab the entry for our dashboard widget and get rid of it from the core_dashboard.
			$cs_custom_dashboard_widget_entry = array( 'cs_custom_dashboard_widget' => $core_dashboard['cs_custom_dashboard_widget'] );
			unset( $core_dashboard['cs_custom_dashboard_widget'] );
			$wp_meta_boxes['dashboard']['normal']['core'] = $core_dashboard; // Re-save the core_dashboard w/o our dashboard widget.
		
			// Now pull the old switcherroooo and re-add our widget to the high_dashboard (high priorty that is, because these get displayed first)
			if( empty( $wp_meta_boxes['dashboard']['normal']['high'] ) ) {
				$wp_meta_boxes['dashboard']['normal']['high'] = $cs_custom_dashboard_widget_entry;
			} else {
				array_push( $wp_meta_boxes['dashboard']['normal']['high'], $cs_custom_dashboard_widget_entry );
			}
		}
		
		/**
		 * This function renders the content of the ClickSold dashboard widget.
		 */
		function cs_custom_dashboard_widget() {
			global $cs_opt_tier_name;

			// Check if the user wants the help links to show.
			$show_help_links = 1; // The default.
			$widget_options = get_option( 'dashboard_widget_options' );
			if( isset( $widget_options['cs_custom_dashboard_widget']['show_help_links'] ) ) {
				$show_help_links = $widget_options['cs_custom_dashboard_widget']['show_help_links'];
			}

			if( $show_help_links ) {

				// If they are below Platinum show the upgrade link.
				if( 'Platinum' != get_option( $cs_opt_tier_name, 'Bronze' ) ) {
					echo	"

						<div class='cs_dashboard_widget_upgrade_img_wrapper'>
						  <a href='admin.php?page=cs_plugin_product_config_direct' class='cs-dashboard-icon'>
						    <div class='cs-dashboard-icon-image'><img src='".plugins_url( 'images/welcome-upgrade.png', __FILE__ ) ."'></div>
						    <div class='cs-dashboard-icon-text'>Upgrade my Account (One month free trial)</div>
						  </a>
						</div>

						";
					
					// Show the popup explaining the upgrade procedure.
					$show_upgrade_popup = get_option("cs_opt_show_upgrade_popup", "1");
					//$show_upgrade_popup = 1;
					if($show_upgrade_popup == "1") {
						update_option("cs_opt_show_upgrade_popup", "0"); // Never show this again.

						echo
							"<script>
								(function($){ 
									$(document).ready(function(){				 
										var csPopupHtml =	\"<div id=\\\"cs_upgrade_popup\\\" class='cs_upgrade_popup'>\" +
															\"  <div class=\\\"cs_dashboard_widget_upgrade_img_wrapper\\\">\" +
															\"    <a href=\\\"admin.php?page=cs_plugin_product_config_direct\\\" target=\\\"_blank\\\"  class='cs-dashboard-icon'>\" +
															\"      <div class='cs-dashboard-icon-image'><img src=\\\"".plugins_url( 'images/welcome-upgrade.png', __FILE__ )."\\\"></div>\" +
															\"      <div class='cs-dashboard-icon-text'>Upgrade my Account (One month free trial)</div>\" + 
															\"    </a>\" +
															\"  </div>\" +

															\"  <div class='cs_dashboard_widget_help_links_msg_wrapper cs_dashboard_widget_help_links_msg_wrapper_with_bottom_border'>\" + 
															\"    <p class='cs_dashboard_widget_help_links_msg_header'>Welcome to ClickSold:</p>\" + 
															\"    <p>Clicking the above image or the same image in the Dashboard widget will allow you to upgrade your account or try a free demo package. Free demo packages allow you to see exactly how your plugin would behave after upgrading to the full ClickSold Platinum package.</p>\" + 
															\"    <p class='cs_dashboard_widget_help_links_msg_header'>Free Tral:</p>\" + 
															\"    <p>ClickSold offers a one month free trial of our flagship Platinum package.</p>\" + 
															\"  </div>\" + 

															\"</div>\" +
															\"\" + 
															\"\";
												
										var csPopupOpts = {
											html : csPopupHtml,
											className : \"cs_offers_popup_modal\",
											width : \"500\",
										};
										
										$.clickSoldUtils(\"infoBoxCreate\", csPopupOpts);
									});
								})(csJQ);
							</script>";
					}
				}

				if( cs_is_hosted() ) { // Only display the welcome to wordpress if it's a ClickSold hosted wordpress install.
				
					echo 	"
						<div class='cs_dashboard_widget_help_links_msg_wrapper cs_dashboard_widget_help_links_msg_wrapper_with_bottom_border'>
						  <p class='cs_dashboard_widget_help_links_msg_header'> Welcome to your new WordPress site: </p>

						  <p>
						    Wordpress is a leading open publishing platform used by thousands of
						    sites on the Internet. Please take a moment to setup your new WordPress
						    website:
						  </p>

						  <table class='cs_dashboard_widget_help_links_link_table'>
						    <tbody>
						      <tr>
						        <td><a target='_blank' href='http://codex.wordpress.org/Themes'>Selecting A Theme</a></td>
						        <td><a target='_blank' href='http://codex.wordpress.org/Pages'>Adding / Editing Pages</a></td>
						      </tr>
						      <tr>
						        <td><a target='_blank' href='http://codex.wordpress.org/WordPress_Widgets'>Managing Widgets</a></td>
						        <td><a target='_blank' href='http://codex.wordpress.org/Posts'>Posting blog entries</a></td>
						      </tr>
						    </tbody>
						  </table>

						  <p>
						    Full documentation on using Wordpress is available directly from the <a href='http://codex.wordpress.org/Main_Page'>wordpress.org</a> site.
						  </p>

						</div>
					";
					
				}
				
				echo 	"
						<div class='cs_dashboard_widget_help_links_msg_wrapper'>
						  <p class='cs_dashboard_widget_help_links_msg_header'> Getting Started With ClickSold: </p>

						  <p>
						    The ClickSold plugin is now enabled on your wordpress site. Please review the
						    following articles to help you get started using ClickSold quickly and effectively.
						  </p>

						  <table class='cs_dashboard_widget_help_links_link_table'>
						    <tbody>
						      <tr>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Main_Page#.C2.A0Getting_Started'>Getting Started</a></td>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Main_Page#.C2.A0The_ClickSold_Menu'>The ClickSold Menu</a></td>
						      </tr>
						      <tr>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Main_Page#.C2.A0Using_Your_Website_Effectively'>Using your Website Effectively</a></td>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/How_to_Add_Profile_Photos'>Add your photo</a></td>
						      </tr>
						      <tr>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Add_a_Listing'>Add a listing</a></td>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Modify/Update_Contact_Info'>Edit your contact information</a></td>
						      </tr>
						      <tr>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Get_Listings_from_Your_MLS%C2%AE'>Get Listings from your MLS&reg;</a></td>
						        <td><a target='_blank' href='http://www.clicksold.com/wiki/index.php/Main_Page#.C2.A0Troubleshooting'>Troubleshooting</a></td>
						      </tr>
						    </tbody>
						  </table>

						  <p>
						    The main page of the ClickSold help documentation can be found at <a href='http://www.clicksold.com/wiki/index.php/Main_Page'>www.ClickSold.com/wiki/</a>
						  </p>

						</div>
				";
			}

//			echo "<p>ClickSold Plugin is enabled. <a href='http://www.ClickSold.com'>Visit</a> for more details. </p>";
		}
		
		/**
		 * This handles the options (just the show help links toggle for now).
		 */
		function cs_custom_dashboard_widget_control_callback() {

			$widget_id = 'cs_custom_dashboard_widget';
			$form_id = 'cs_custom_dashboard_widget-control';
    
			// Checks whether there are already dashboard widget options in the database
			if ( !$widget_options = get_option( 'dashboard_widget_options' ) ) {
				$widget_options = array();
			}

			// Check whether we have information for this form
			if ( !isset( $widget_options[$widget_id] ) ) {
				$widget_options[$widget_id] = array();
			}
    
			// Check whether our form was just submitted
			if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

				// The value here does not matter, if the option is there it's checked otherwise it's not.
				if( isset( $_POST[$form_id . '-show-help-links'] ) ) {
					$widget_options[$widget_id]['show_help_links'] = 1;
				} else {
					$widget_options[$widget_id]['show_help_links'] = 0;
				} 
     
				update_option( 'dashboard_widget_options', $widget_options ); // Update our dashboard widget options so we can access later
			}

			// Grab the previous value.
			$show_help_links = isset( $widget_options[$widget_id]['show_help_links'] ) ? $widget_options[$widget_id]['show_help_links'] : '1';
  
			$checked = 'checked';
			if( !$show_help_links ) {
				$checked = '';
			}

			// Create our form fields. Pay very close attention to the name part of the input field.
			echo '<p><label for="cs_custom_dashboard_widget-show-help-links">' . __('Show Help Links:') . '</label>';
			echo '<input id="cs_custom_dashboard_widget-show-help-links" name="'.$form_id.'-show-help-links" type="checkbox" value="show-help-links" '.$checked.' /></p>';

		}


		/** Product Upgrade **/
		
		/**
		 * Function used for displaying the upgrade notification page
		 */
		function cs_plugin_admin_upgrade_page() {
			echo "";
		}
		
		/**
		 * General function used for outputting page content for pages generated by the ClickSold server
		 */
		function cs_generated_form(){

			global $cs_opt_first_login;

			/** If this is the first access to the ClickSold back office, show a welcome message. **/

			// Display the message if we're hosted and it's the first login.
			if ( cs_is_hosted() && get_option( $cs_opt_first_login, 1 ) ) {
				echo '<div id="message" class="updated highlight" id="message"><p><strong>Congratulations! Welcome to ClickSold.</strong></p></div>';
			}

			// Record that any subsequent access is no longer the first login.
			update_option( $cs_opt_first_login, 0 );

			echo $this->response->get_body_contents();
		}
		
		/**
		* Function used to add/remove CS generated pages based on the old and new plugin tiers.
		*
		*/
		function cs_plugin_toggle_product(){
		
			$wp_rewrite->flush_rules();
		}
		
		/**
		 * Function that prevents ClickSold generated pages from being deleted via Pages/Edit Page
		 */
		function prevent_cs_trash_pages($post_id){
		
			global $wpdb;
			global $cs_posts_table;
			
			if(!is_null($wpdb->get_var('SELECT postid FROM ' . $wpdb->prefix . $cs_posts_table . ' WHERE postid = ' . $post_id ))) {
				wp_redirect(admin_url()."edit.php?post_type=page&cs_page_del_error=true");
				exit();
			}
		}

		/**
		 * Function that displays an error message when a user attempted to delete a CS page
		 */
		function display_cs_page_delete_error(){
		
			echo '<div id="message" class="error">' .
				 '  <p>' .
				 '    <strong>' .
				 'ClickSold generated pages cannot be removed.' .
				 '    </strong>' .
				 '  </p>' .
				 '</div>';
		}
		
		/**
		 * Hides unavailable cs pages from the admin pages view
		 */
		function hide_unavailable_cs_pages($wp_query){
			global $wpdb;
			global $cs_posts_table;
			
			$cs_post_ids = $wpdb->get_col('SELECT postid FROM ' . $wpdb->prefix . $cs_posts_table . ' WHERE available = 0');
			if(!empty($cs_post_ids)) $wp_query->query_vars['post__not_in'] = $cs_post_ids;
		}
	}
?>
