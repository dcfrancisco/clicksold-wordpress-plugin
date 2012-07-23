<?php
/*
Plugin Name: ClickSold IDX
Author: ClickSold | <a href="http://www.ClickSold.com">Visit plugin site</a>
Version: 1.3
Description: This plugin allows you to have a full map-based MLS&reg; search on your website, along with a bunch of other listing tools. Go to <a href="http://www.clicksold.com/">www.ClickSold.com</a> to get a plugin key number.
Author URI: http://www.ClickSold.com/
*/
require_once('cs_constants.php');

global $cs_db_version; 
$cs_db_version =  "1.1"; //change this db version,deactivate,activate the plugin
			  //to regenerate the table that it uses

global $cs_posts_table;
$cs_posts_table = "cs_posts";

// options we will add to the wp_options table
global $cs_opt_plugin_key;	/** NOTE: These are also used in wp-mu-control.php plz update that as well if changing the names of the options. **/
$cs_opt_plugin_key     = "cs_opt_plugin_key";
global $cs_opt_plugin_num;
$cs_opt_plugin_num     = "cs_opt_plugin_num";
global $cs_opt_plugin_hostname;
$cs_opt_plugin_hostname = "cs_opt_plugin_hostname";
global $cs_opt_plugin_version;
$cs_opt_plugin_version  = "cs_opt_plugin_version";
global $cs_opt_brokerage;
$cs_opt_brokerage = "cs_opt_brokerage";
global $cs_change_products_request;
$cs_change_products_request = "cs_change_products_request";
global $cs_opt_first_login;
$cs_opt_first_login = "cs_opt_first_login";
global $cs_opt_tier_name;
$cs_opt_tier_name = "cs_opt_tier_name";

// options for the auto blogger
global $cs_autoblog_new;
$cs_autoblog_new = 'cs_autoblog_new';
global $cs_autoblog_sold;
$cs_autoblog_sold = 'cs_autoblog_sold';
global $cs_autoblog_last_update;
$cs_autoblog_last_update = 'cs_autoblog_last_update';
global $cs_autoblog_freq;
$cs_autoblog_freq = 'cs_autoblog_freq';
global $cs_autoblog_default_post_title_active;   // These four are the default values.
global $cs_autoblog_default_post_title_sold;     //     "
global $cs_autoblog_default_post_content_active; //     "
global $cs_autoblog_default_post_content_sold;   //     "
$cs_autoblog_new_title = 'cs_autoblog_new_title';
$cs_autoblog_new_content = 'cs_autoblog_new_content';
$cs_autoblog_sold_title = 'cs_autoblog_sold_title';
$cs_autoblog_sold_content = 'cs_autoblog_sold_content';

// initial values for this plugin. By default the plugin key 
// and plugin number are empty. These values can be updated
// once the plugin is activated by calling ClickSold.
global $cs_plugin_options;
$cs_plugin_options = array(
  $cs_opt_plugin_key     => "", 
  $cs_opt_plugin_num     => "", 
  $cs_opt_plugin_version => "1.0",
  $cs_opt_brokerage => "0",
  $cs_change_products_request => "0",
  $cs_autoblog_new => "0",
  $cs_autoblog_sold => "0",
  $cs_autoblog_last_update => "0",
  $cs_autoblog_freq => "1",
  $cs_autoblog_new_title => $cs_autoblog_default_post_title_active,
  $cs_autoblog_new_content => $cs_autoblog_default_post_content_active,
  $cs_autoblog_sold_title => $cs_autoblog_default_post_title_sold,
  $cs_autoblog_sold_content => "hello" . $cs_autoblog_default_post_content_sold . "hello"
);   

global $cs_logo_path;
$cs_logo_path = plugins_url("orbGreen.png", __FILE__);

global $cs_response;

global $wpdb;

//Include the WP_Http class used for making HTTP requests
if ( !class_exists( 'WP_Http' ) ) :
include_once( ABSPATH . WPINC. '/class-http.php' );
endif;

//Include the CS_Rewrite class to create dynamic rewrite rules for the plugin
if( !class_exists('CS_rewrite') ):
include_once( plugin_dir_path(__FILE__) . 'CS_rewrite.php');
endif;

require_once('CS_request.php');
require_once('CS_response.php');
require_once('CS_shortcodes.php');
require_once('CS_admin.php');
require_once('CS_config.php');
require_once('CS_utilities.php');

require_once(ABSPATH. 'wp-includes/pluggable.php');

//hook add_query_vars function to query_vars.
//query_vars: applied to the list of public WordPress query variables before the SQL query is formed. 
//            Useful for removing extra permalink information the plugin has dealt with in some other manner.
add_filter('query_vars', 'cs_add_query_vars');
function cs_add_query_vars($aVars) {

	global $wpdb;
	global $cs_posts_table;
	$table_name = $wpdb->prefix . $cs_posts_table;
	
	//grab each parameter from the db and add it to list of query variables. Using GROUP BY
	//clause here since we want to eliminate the duplicate parameters
	$result = $wpdb->get_results("SELECT parameter FROM $table_name GROUP BY parameter" );
	foreach($result as $parameter){
		$aVars[] = $parameter->parameter;
	}
	return $aVars;

}

//hook to rewrite_rules_array. This filter is checked every time you save/re-save your permalink structure
add_filter('rewrite_rules_array', 'cs_add_rewrite_rules');
function cs_add_rewrite_rules($aRules) {
	global $wpdb;
	global $cs_posts_table;
		
	//get all posts we know are from ClickSold. Query wp_cs_posts
	$table_name = $wpdb->prefix . $cs_posts_table;
	$cs_posts = $wpdb->get_results( "SELECT postid FROM $table_name GROUP BY postid" ); //gets unique post ids
	// create IN Clause ex. "(1,2,3)" etc.
	
	$post_id_str = "(";	
	for($i = 0; $i < count($cs_posts); $i++){
		if( $i != count($cs_posts) - 1)
			$post_id_str = $post_id_str . $cs_posts[$i]->postid . ",";
		else $post_id_str = $post_id_str . $cs_posts[$i]->postid;
	}
	$post_id_str = $post_id_str . ")";
	
	$wp_posts = $wpdb->get_results( "SELECT ID, post_title, post_name FROM $wpdb->posts WHERE ID IN $post_id_str" );
		
	foreach($wp_posts as $post){
		$parameters_array = array(); // array that will contain all the parameters associated with a postid
		$parameters = $wpdb->get_results("SELECT parameter FROM $table_name WHERE postid = $post->ID");
		$i = 0;
		foreach($parameters as $param){
			$parameters_array[$i]= $param->parameter; //store the parameter in an array
			$i = $i + 1;
		}
	
		//get all subpages that have this ClickSold page as its parent
		$sub_pages = $wpdb->get_results( "SELECT ID, post_name FROM $wpdb->posts WHERE post_parent = $post->ID AND post_type = 'page'" );

		//now we have list of parameters ($parameters_array)
		//and we have reference to the post_name ($post->post_name) -> create the rewrite rules
		$cs_rewrite = new CS_rewrite($post->post_name, $parameters_array, $sub_pages, false);
		$aNewRules = $cs_rewrite->getRewriteRuleArray();
		$aRules = $aNewRules + $aRules;
	}
	
	//above lines actually generate the commented out code below, but, dynamically!
	/*$aNewRules = array('listings/?$' => 'index.php?pagename=listings',
               'listings/([^/]+)/?$' => 'index.php?pagename=listings&mlsnum=$matches[1]',
	       'neighbourhoods/?$' => 'index.php?pagename=neighbourhoods',
	       'neighbourhoods/([^/+]+)/?$' => 'index.php?pagename=neighbourhoods&neighbourhood=$matches[1]');

	
	$aRules = $aNewRules + $aRules;*/
		
	return $aRules;
}

/**
 * Init the session early (needed so the cs plugin server does not need to generate a new session for each request).
 */
if(! function_exists('cs_init_session') ) {
	function cs_init_session() {
		if(!session_id()){
			session_start();
		}
	}
	add_action('init', 'cs_init_session', 1);
}

add_action("init", "check_product_update");

/**
 * Check for new update. If we have changes make request: which features are available.
 */
function check_product_update(){

	global $cs_change_products_request;
	global $wpdb;
	global $CS_SECTION_PARAM_CONSTANTS;
	global $CS_SECTION_ADMIN_PARAM_CONSTANT;
	global $cs_opt_brokerage;
	global $cs_opt_tier_name;
	global $cs_opt_plugin_key;
	global $cs_opt_plugin_num;
	
	if ( get_option( $cs_change_products_request ) == "1" && !get_option( $cs_opt_plugin_key, "" ) == "" && !get_option( $cs_opt_plugin_num, "" ) == "" ) {
		//make request to RPM server about allowed features
		$cs_request = new CS_request( "tier_validate", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"] );//error_log( "Response: " . print_r( $cs_request->request(), true ) );
		$cs_response = new CS_response( $cs_request->request() );
		$vars = $cs_response->cs_set_vars(); //error_log( "vars: " . print_r( $vars, true ) );
		
		if(empty($vars)) {
			//Invalidate / Hide plugin pages
			$cs_pages = $wpdb->get_col("SELECT postid FROM " . $wpdb->prefix . "cs_posts");
			if(!empty($cs_pages)) {
				$wpdb->query('UPDATE ' . $wpdb->prefix . 'posts SET post_status = "private" WHERE ID IN(' . implode(", ", $cs_pages) . ')');
				$wpdb->query('UPDATE ' . $wpdb->prefix . 'cs_posts SET available = 0 WHERE 1;');
			}
			return;
		}
		
		$page_on_front = get_option( 'page_on_front' );
		$cs_posts_desired_statuses = get_option( "cs_posts_desired_statuses", array() ); // Note these are the desired statuses (if a feature is available a private here will override the publish that comes from the feature being available) the code treats missing entries as publish so this one gets a default empty array.

		// Toggling the brokerage needs to be done before the tier checks because when it saves / restores the state of the associates page which needs to happen before the feature availability calculations.
		$cs_config = new CS_config();
		$cs_config->cs_plugin_check_brokerage($vars["brokerage"]);

		// For each section (tier_feature ie: idx, associates), update the status / diaplay of the associated pages.
		foreach( $CS_SECTION_PARAM_CONSTANTS as $tier_feature ) {

			$feature_post_id = $wpdb->get_var('SELECT postid FROM ' . $wpdb->prefix . "cs_posts" . ' WHERE PREFIX = "' . $tier_feature . '"');

			if ( $feature_post_id > 0 && $vars[$tier_feature] != "" ) { // If this tier_feature has an associated post AND it's one of the tier_features reported by the cs server.

				$postStatus = ( $vars[$tier_feature] === "true" && ( !isset( $cs_posts_desired_statuses[$tier_feature] ) || $cs_posts_desired_statuses[$tier_feature] == "publish" ) )?"publish":"private";	// All posts associated with available features are set to publish, if not available set to private (this keeps them in or out of dynamically created menus) -- unless of course an available feature marked as do not show.
				$wpdb->update( $wpdb->posts, array( "post_status" => $postStatus ), array( "ID" => $feature_post_id ), array( "%s" ), array( "%d" ) );
				$wpdb->update( $wpdb->prefix . "cs_posts", array( "available" => ( $vars[$tier_feature] === "true" )?"1":"0" ), array( "postid" => $feature_post_id ), array( "%s" ), array( "%d" ) ); // The available field on the cs_posts table controls which sections of the cs back office are disabled.

				// If this is set as the front page but cannot be shown, set the front page as the WordPress default (latest posts)
				if($page_on_front == $feature_post_id && $vars[$tier_feature] !== "true") {
					update_option('page_on_front', 0);
					update_option('show_on_front', 'posts');
				}

				// If the post can't be shown we can't just set it as private, we also have to remove it from the custom menus.
				if( $vars[$tier_feature] !== "true" ) {

					// Find and remove all of the menu references to this feature's post.
					foreach( wp_get_associated_nav_menu_items( $feature_post_id ) as $feature_menu_item_id ) {

						$wpdb->query( "DELETE from ".$wpdb->posts." WHERE ID = '".$feature_menu_item_id."'" );
						$wpdb->query( "DELETE from ".$wpdb->postmeta." WHERE post_id = '".$feature_menu_item_id."'" );
						$wpdb->query( "DELETE from ".$wpdb->term_relationships." WHERE object_id = '".$feature_menu_item_id."'" );
					}
				} else if( $vars[$tier_feature] === "true" && count( wp_get_associated_nav_menu_items( $feature_post_id ) ) == 0 ) { // If the feature of the page is available but has no associated menu items we have to add it to the custom menus.
					
					// Add the page to the menus.
					if( get_option("cs_allow_manage_menus", 1) ) {
						cs_add_post_to_custom_menus( $feature_post_id, 'page', $postStatus ); // We know that these are pages as we don't have feature dependant posts.
					}
				}
			}
		}
		
		if (($vars["isWaitingForUpdate"] != "") && ($vars["isWaitingForUpdate"] == "false")) {
			update_option($cs_change_products_request, "0");
		}

		if ( $vars["tierName"] != "" ) {
			update_option( $cs_opt_tier_name, $vars["tierName"] );
		}

	}
}
	
$post_param = "";
$page_vars = array();  //Global Variable for holding page variables
$meta_config = array();

// Auto login processing
add_action('login_head', 'attempt_autologin_auth');
function attempt_autologin_auth(){
	if( !empty($_GET['name']) && !empty($_GET['pass']) ){
		$creds = array(
			'user_login' => $_GET['name'],
			'user_password' => $_GET['pass'],
			'remember' => false
		);
		$user = wp_signon($creds, false);
		if ( !is_wp_error($user) ) {
			wp_set_current_user($user->ID);
			wp_safe_redirect(admin_url());
		}
	}
}

/**
* Checks the server to see if this account's mobile site is disabled
*/
function cs_mobile_site_disabled() {
	$cs_request = new CS_request("pathway=611", "");
	$cs_response = new CS_response($cs_request->request());
	$val = $cs_response->get_body_contents();
	$val = trim($val);
	if($val == "false") return false;
	else return true;
}

// hijack the post action only if we are in the front of the website
if( !is_admin() ){
	
	global $wp_rewrite;
	if(get_option("page_on_front") > 0 && get_option("permalink_structure") !== NULL) remove_filter('template_redirect', 'redirect_canonical');
	
	//if(!is_404() && !is_preview()){
		
		// Check if we need to blog listing updates
		add_action('pre_get_posts', 'cs_listing_auto_blog_update');
				
		// For handling mobile site stuff
		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			if((stripos(basename($_SERVER['REQUEST_URI']), 'cs_mobile.php') === FALSE) && (!isset($_COOKIE["csFullSite"]) || $_COOKIE["csFullSite"] != "true") && (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== FALSE || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== FALSE) ){
				if(cs_mobile_site_disabled() == false) {
					header('location:' . plugin_dir_url(__FILE__) . 'cs_mobile.php');
					die();
				}
			}
		}
		// For handling VIP confirmation links
		if(!empty($_GET["pathway"]) && !empty($_GET["email_addr"]) && !empty($_GET["confirmationCode"])){
			add_action('parse_query', 'cs_process_vip_confirmation', 5);
			
		// For handling VIP saved search links
		}else if(!empty($_GET["s_s"])){
			add_action('init', 'cs_saved_search_redirect', 5);
			
		// Normal page handling
		}else{
			// Adds inline javascript that changes masked domains to their original urls
			add_action('parse_query', 'cs_process_cs_section_posts', 5); 	// ClickSold section posts are processed in parse_query because they need to be able to set the title.
			add_action('wp', 'cs_process_cs_shortcode_posts'); 	// ClickSold shortcodes are processed when we are processing the post itself.	
			add_action('wp', 'cs_disable_domain_mask');			
		}
	//}
	
	/**
	 * Outputs frame breaking javascript
	 */
	function disable_domain_masking(){
		print '<script type="text/javascript">/*<![CDATA[*/' . 
		'  if(window.top !== window) { ' .
		'    top.location = window.location.href; ' .
		'  } ' .
		'/*]]>*/</script>';
	}
	
	/**
	 * Call to set frame breaking code in the header.  Needed to be called via the wp action as is_admin() would always return false otherwise.
	 */
	function cs_disable_domain_mask(){
		global $cs_opt_tier_name;
		global $cs_opt_brokerage;
				
		if( get_option( $cs_opt_tier_name, 'Bronze' ) === "Bronze" && get_option( $cs_opt_brokerage ) != "1" && !is_admin() ) {
			add_action("wp_print_scripts", "disable_domain_masking");  // Unmask domains for Bronze tier
		}
	}
	
	/**
	 * Checks options to see if we should run the listing auto blogger
	 */
	function cs_listing_auto_blog_update(){
		global $wpdb;
		global $cs_autoblog_new;
		global $cs_autoblog_sold;
		global $cs_autoblog_last_update;
		global $cs_autoblog_freq;
		
		//DEBUG - could possibly just leave it here and prevent these options from being added on plugin init
		if( get_option($cs_autoblog_new) === false ) { add_option($cs_autoblog_new, "0"); }
		if( get_option($cs_autoblog_sold) === false ) { add_option($cs_autoblog_sold, "0"); }
		
		if( get_option($cs_autoblog_new) == "1" || get_option($cs_autoblog_sold) == "1" ) {
			$last_update = get_option($cs_autoblog_last_update);
			if(!empty($last_update)){
				//Compare now with last update date
				$now = new DateTime(date(DATE_ATOM));
				$last_update = new DateTime(date(DATE_ATOM, $last_update));
				$freq = get_option($cs_autoblog_freq);
				
				//Skip update if number of days is not past the frequency (days before next update)
				if( $now->diff($last_update)->days < $freq ) {
					//error_log('Skipping Update');
					return;
				}
			}
			//Run update
			$cs_utils = new CS_utilities();
			$cs_utils->listing_autoblog_get_listing_posts();
		}
	}
	
	function cs_saved_search_redirect(){
		global $wpdb;
		global $CS_SECTION_PARAM_CONSTANTS;
		global $wp_rewrite;
		
		// Get / construct MLS search URL
		$cs_posts = $wpdb->prefix . "cs_posts";
		$pageid = $wpdb->get_var('SELECT postid FROM ' . $cs_posts . ' WHERE prefix = "' . $CS_SECTION_PARAM_CONSTANTS['listings_pname']  . '"');
		
		if(is_null($pageid)) return;
		
		$vars = $_GET;
		unset($vars['s_s']);  // Safe to keep but let's remove it anyways
		//$link = get_page_uri($pageid);
		$link = get_permalink($pageid);
		
		if(empty($link)) return;
		
		if($wp_rewrite->using_permalinks()){
			$link .= "?" . http_build_query($vars);
		}else{
			$link .= "&" . http_build_query($vars);
		}
		
		//error_log("Listings Saved Search URI: " . $link);
		
		// Run redirect to site
		echo "<script type=\"text/javascript\">";
		echo "location.href=\"" . $link . "\";";
		echo "</script>";
	}
	
	/**
	* Removes the edit link from the template itself if the current page is one generated from this plugin.
	*/
	add_filter('edit_post_link', 'remove_edit_post_link');
	function remove_edit_post_link( $link ){
		global $wpdb;
		global $wp_admin_bar;
		
		if(!in_the_loop()) wp_reset_query();
		
		$cs_posts = $wpdb->prefix . "cs_posts";
		$cs_page_ids = $wpdb->get_col('SELECT postid FROM ' . $cs_posts);
		
		if(is_page($cs_page_ids)){
			return ''; 
		}else{
			return $link;
		}
	}
		
	/**
	* Removes the "Edit Page" link from the admin bar if the current page is one generated from this plugin.
	*/
	add_action('wp_before_admin_bar_render', 'remove_admin_bar_edit');
	function remove_admin_bar_edit(){
		global $wpdb;
		global $wp_admin_bar;
		
		if(!in_the_loop()) wp_reset_query();
		
		$cs_posts = $wpdb->prefix . "cs_posts";
		$cs_page_ids = $wpdb->get_col('SELECT postid FROM ' . $cs_posts);
		
		if(is_page($cs_page_ids)) $wp_admin_bar->remove_menu('edit');
	}
		
	/**
	 * Surrounds the contents of the page with a div wrapper for use with our styles. 
	 * Used in conjunction with add_filter - the_content
	 * @param unknown_type $content
	 */
	if( !function_exists( 'cs_styling_wrap' ) ) {
		function cs_styling_wrap ( $content ){
			return "<div id=\"cs-wrapper\">" . $content . "</div>";
		}
	}

	/**
	 * Processes VIP confirmation links
	 */
	function cs_process_vip_confirmation( $wp_query ){
		global $CS_SECTION_PARAM_CONSTANTS;
	
		remove_action('parse_query', 'cs_process_vip_confirmation', 5);
		$cs_request = new CS_request(http_build_query($_GET), $CS_SECTION_PARAM_CONSTANTS["listings_pname"]);
		
		$cs_response = new CS_response($cs_request->request());
		
		// make sure the_content hook calls our functions to load the response in the appropriate spot
		add_action("wp_head", array($cs_response, "cs_get_header_contents_linked_only"), 0);
		add_action("wp_head", array($cs_response, "cs_get_header_contents_inline_only"), 11); // Needs to be ran at a highier priority as it needs to go AFTER the enqueue stuff.
		add_action("wp_footer", array($cs_response, "cs_get_footer_contents"), 0);
	}
	
	/**
	 * process the request as an cs request if the post id matches
	 * one of the ClickSold Plugin sections.
	 */
	function cs_process_cs_section_posts( $wp_query ){

		remove_action('parse_query', 'cs_process_cs_section_posts', 5);
		
		global $wpdb;
		global $wp_rewrite;
		global $cs_response;
		global $cs_posts_table;
		global $cs_opt_plugin_key, $cs_opt_plugin_num, $cs_opt_plugin_version;
		global $cs_opt_tier_name;
		global $cs_opt_brokerage;
		
		// Global vars needed for configuring meta tags
		global $post_param;
		global $page_vars;
		global $meta_config;
		
		/** Check for and process ClickSold Section pages (eg: listings/, communities/ or idx/). **/

		$table_name = $wpdb->prefix . $cs_posts_table;
		
		// We fetch the post id differently depending on if permalinks are enabled or not.
		if( $wp_rewrite->using_permalinks()) {

			$post_id = $wp_query->get_queried_object_id();

			//Check to see if this is one of our pages as the front page.
			//Note that we can't use is_front_page() as it is too early in the loop
			//to get the proper response.
			if(empty($post_id)) $post_id = $wp_query->query_vars["page_id"];
			
		} else $post_id = $wp_query->get( "page_id" ); // NOTE: calling $wp_query->get_queried_object_id() does NOT work here... likely too early in the processing.
		
		//error_log(print_r( $wp_query, true ));
		// print ( "<br>(" . $post_id . ")<br>" );
		
		if(!empty($post_id)){
			$result = $wpdb->get_row( "SELECT postid, defaultpage, prefix, parameter, header_title, header_desc, header_desc_char_limit FROM $table_name WHERE postid = $post_id", ARRAY_A );
			
			if($result != null){
				
				// The post matches one that cs added for the user
				// process the request using the cs plugin server.
				if($result['postid'] == $post_id){
					$cs_org_req = "";
					
					if(array_key_exists($result['parameter'], $wp_query->query_vars)) {
						$param = $wp_query->query_vars[$result['parameter']];
					} else {
						$param = "";
					}
					
					if(!empty($param)){
						$post_param = $result['parameter'];
						$cs_org_req = $param;
						// If present, append GET query string to cs_org_req
						// Note: primarily used for featured listings view
						if(!empty($_GET)){
							$cs_org_req .= "?" . http_build_query($_GET);
						}
					// If no parameters were returned from the database, give cs_org_req the value of the GET query string if available
					}else if(!empty($_GET)){ 
						$cs_org_req = http_build_query($_GET);
					// If this page was set as a front page, we need to feed in the request manually
					}else if($post_id == get_option( "page_on_front" ) && !$wp_rewrite->using_permalinks()){ 
						$cs_org_req = "page_id=" . $post_id;
					}
					
					// K, here we skip calls to the plugin server if we're just requesting resource files.
					if( preg_match( '/\.png$|\.gif$/s', $cs_org_req ) ) { // If it's any of the known types.
						return;
					}
					
					$cs_request = new CS_request($cs_org_req, $result['prefix']);
					$cs_response = new CS_response($cs_request->request());
					
					$page_vars = $cs_response->cs_set_vars();
					$meta_config = array('header_title' => $result['header_title'], 'header_desc' => $result['header_desc'], 'header_desc_char_limit' => $result['header_desc_char_limit']);
					
					// Configure the account type based on the account config value given
					/*
					if(!empty($page_vars)) {
						$cs_config = new CS_config();
						$cs_config->cs_plugin_check_brokerage($page_vars);
					}
					*/
					
					// make sure the_content hook calls our functions to load the response in the appropriate spot
					add_filter("wp_title", "cs_set_head_title", 0);
					add_action("wp_head", "cs_set_meta_desc", 1);
					add_action("wp_head", array($cs_response, "cs_get_header_contents_linked_only"), 0);
					add_action("wp_head", array($cs_response, "cs_get_header_contents_inline_only"), 11); // Needs to be ran at a highier priority as it needs to go AFTER the enqueue stuff.
					add_action("wp_footer", array($cs_response, "cs_get_footer_contents"), 0);
					remove_filter("the_content", "wpautop");  //This line prevents wordpress from replacing double line breaks with <br> tags i.e. messes up the pagination sections in listing results views
					add_filter("the_content", array($cs_response, "get_body_contents"), 1);
					add_filter("the_content", "cs_styling_wrap", 2); //This line wraps all content around a div so our styles can take precedence over the template styles
				}
			}
		}
	}

	/**
	 * Process the request as an ClickSold request if the content contains any cs_shortcodes.
	 */
	if( !function_exists( 'cs_process_cs_shortcode_posts' ) ) {
		function cs_process_cs_shortcode_posts( $wp ){
			$cs_shortcodes = new CS_shortcodes( "cs_listings" );
			$cs_shortcodes->cs_process_cs_shortcode_posts( $wp ); // Defer to the shortcodes class for this as the code is similar between our plugins.
		}
	} # End if function_exists
		
	/**
	 * Sets the meta title tag for ClickSold generated pages
	 */
	function cs_set_head_title($title){
		
		remove_filter("wp_title", "cs_set_head_title", 0);
		
		global $post;
		global $wp_query;
		global $CS_VARIABLE_LISTING_META_TITLE_VARS;
		global $CS_VARIABLE_COMMUNITY_META_TITLE_VARS;
		global $CS_VARIABLE_ASSOCIATE_META_TITLE_VARS;
		global $CS_GENERATED_PAGE_PARAM_CONSTANTS;
		
		global $page_vars;
		global $meta_config;
		global $post_param;
		
		$options = array();
		
		//Return the original title if any of the required config arrays are empty
		if(empty($page_vars)|| empty($meta_config) || empty($post_param)) return $title;
		
		//This will tell us if this page is a generated page - if not, return the original title value
		if(!key_exists($post_param, $wp_query->query_vars)) return $title;
		
		/* NOTE: Subject to change once we decide on keying pages for use with this *
		 * plugin                                                                   */
		if($post_param == $CS_GENERATED_PAGE_PARAM_CONSTANTS['listings']){
			$options = $CS_VARIABLE_LISTING_META_TITLE_VARS;
		}else if($post_param == $CS_GENERATED_PAGE_PARAM_CONSTANTS['community']){
			$options = $CS_VARIABLE_COMMUNITY_META_TITLE_VARS;
		}else if($post_param == $CS_GENERATED_PAGE_PARAM_CONSTANTS['associates']){
			$options = $CS_VARIABLE_ASSOCIATE_META_TITLE_VARS;
		}
		
		// This is the configured format.
		$cs_title = $meta_config['header_title'];
		
		// If the cs_title configured format is blank, we can just quit right here as there is nothing for us to do.
		if( $cs_title == '' ) { return; }

		//replace wild cards with content, if found
		foreach($options as $key => $value){
			$val_spaced = $value . " ";  //check for values that aren't prepended to any other text e.g. %a is valid, %addr is invalid
			if(strpos($cs_title, $val_spaced) !== false){
				$cs_title = str_replace($val_spaced, $page_vars[$key] . " ", $cs_title);
			}
			
			$offset = strlen($cs_title) - strlen($value);
			if(($offset >= strlen($cs_title) || $offset >= strlen($value)) && $offset < 1){
				if($cs_title == $value){ $cs_title = $page_vars[$key]; }
			}else if(substr_compare($cs_title, $value, strlen($cs_title) - strlen($value)) === false){
				//Do nothing, the check above should of fell through instead
			}else if(substr_compare($cs_title, $value, strlen($cs_title) - strlen($value)) == 0){
				$cs_title = substr_replace($cs_title, $page_vars[$key], $offset, strlen($value));
			}
		}
		
		return $cs_title . " ";
	}

	/**
	 * Sets the meta description tag for ClickSold generated pages
	 */
	function cs_set_meta_desc(){
		
		global $CS_VARIABLE_LISTING_META_DESC_VAR;
		global $CS_VARIABLE_ASSOCIATE_META_DESC_VAR;
		global $CS_VARIABLE_COMMUNITY_META_TITLE_VARS;  //Title vars are also available for description
		global $CS_GENERATED_PAGE_PARAM_CONSTANTS;
		
		global $post;
		global $wp_query;
		
		global $post_param;
		global $page_vars;
		global $meta_config;
		
		$options = array();
		
		if(empty($page_vars) || empty($meta_config) || empty($post_param)) { return; }
		
		if(!key_exists($post_param, $wp_query->query_vars)) { return; }
		
		$char_limit = (int) $meta_config['header_desc_char_limit'];
		
		if($char_limit <= 0){  
			return;
		}else if($char_limit > 200){
			$char_limit = 200;
		}
		
		// This is the configured format.
		$content = $meta_config['header_desc'];
		
		// If the content configured format is blank, we can just quit right here as there is nothing for us to do.
		if( $content == '' ) { return; }
		
		/* NOTE: Subject to change once we decide on keying pages for use with this *
		 * plugin                                                                   */
		if($post_param == $CS_GENERATED_PAGE_PARAM_CONSTANTS['listings']){
			$options = $CS_VARIABLE_LISTING_META_DESC_VAR;
		}else if($post_param == $CS_GENERATED_PAGE_PARAM_CONSTANTS['community']){
			$options = $CS_VARIABLE_COMMUNITY_META_TITLE_VARS;
		}else if($post_param == $CS_GENERATED_PAGE_PARAM_CONSTANTS['associates']){
			$options = $CS_VARIABLE_ASSOCIATE_META_DESC_VAR;
		}
				
		//replace wild cards with content, if found
		foreach($options as $key => $value){
			$pv_val = "";
			if(array_key_exists($key, $page_vars)) $pv_val = $page_vars[$key];
		
			$val_spaced = $value . " ";
			if(strpos($content, $val_spaced) !== false){
				$content = str_replace($val_spaced, $pv_val . " ", $content);
			}
			
			$offset = strlen($content) - strlen($value);
			
			if(($offset >= strlen($content) || $offset >= strlen($value)) && $offset < 1){
				if($content == $value){ $content = $pv_val; }
			}else if(substr_compare($content, $value, $offset) === false){ 
				//Do nothing, the check above should of fell through instead
			}else if(substr_compare($content, $value, $offset) == 0){
				$content = substr_replace($content, $pv_val, $offset, strlen($value));
			}
		}
		
		if(strlen($content) > $char_limit){
			$content = substr($content, 0, $char_limit - 3);
			$content .= "...";
		}
		
		echo "<meta name='description' content='$content' />";
	}
	
}
/* Hooks for plugin activation/deactivation ****************************************************/
$cs_config = new CS_config();
register_activation_hook(__FILE__, array($cs_config, 'cs_activate'));
register_deactivation_hook(__FILE__, array($cs_config, 'cs_deactivate'));

/* Administration Section **********************************************************************/
$load_widgets = true;

if ( is_admin() ) {
	$cs_admin = new CS_admin();

	$cs_shortcodes = new CS_shortcodes( 'cs_listings' );
	add_action('init', array( $cs_shortcodes, 'cs_add_tinymce_buttons' ) );
	
	//Do server check to see if creds are valid - for widgets page only
	if( isset($pagenow) ) {
		$is_wpmu = cs_is_multsite();
		if( empty($is_wpmu) && 'widgets.php' == $pagenow ) {
			$cs_request = new CS_request("pathway=20", "wp_admin");
			$cs_response = new CS_response($cs_request->request());
			$resp = $cs_response->get_body_contents();
			$resp = trim($resp);
			if( !empty($resp) ) $load_widgets = false;
		}
	}
}

/* Load the ClickSold widgets ******************************************************************/
if ( $load_widgets == true && get_option("cs_db_version", FALSE) != FALSE ) {  //Option check is to make sure the next query doesn't get executed on first setup
	if( !class_exists('Personal_Profile_Widget') && !class_exists('Brokerage_Info_Widget') && 
		!class_exists('Mobile_Site_Widget') && !class_exists('Buying_Info_Widget') && 
		!class_exists('Selling_Info_Widget') && !class_exists('Listing_QS_Widget') && 
		!class_exists('Feature_Listing_Widget')):
	include_once( plugin_dir_path(__FILE__) . 'widgets.php');
	endif;

	/* Load the widgets on widgets_init ************************************************************/
	add_action('widgets_init', create_function('', 'register_widget("Personal_Profile_Widget");'));
	add_action('widgets_init', create_function('', 'register_widget("Brokerage_Info_Widget");'));
	add_action('widgets_init', create_function('', 'register_widget("Mobile_Site_Widget");'));
	add_action('widgets_init', create_function('', 'register_widget("Buying_Info_Widget");'));
	add_action('widgets_init', create_function('', 'register_widget("Selling_Info_Widget");'));
	add_action('widgets_init', create_function('', 'register_widget("Feature_Listing_Widget");'));
	add_action('widgets_init', create_function('', 'register_widget("VIP_Widget");'));

	/* Add these widgets if the IDX search page is available */	
	if(!is_null($wpdb->get_var('SELECT postid FROM ' . $wpdb->prefix . $cs_posts_table . ' WHERE prefix = "' . $CS_SECTION_PARAM_CONSTANTS['idx_pname'] . '" AND available = 1'))){
		add_action('widgets_init', create_function('', 'register_widget("IDX_Search_Widget");'));
		add_action('widgets_init', create_function('', 'register_widget("Listing_QS_Widget");'));
	}
}
	
/** Customize the footer on Genesis themes for Managed and Hosted packages */
if(cs_is_hosted() && cs_is_multsite()){
	remove_action( 'genesis_footer', 'genesis_do_footer' );
	add_action( 'genesis_footer', 'child_do_footer' );
	function child_do_footer() {
		echo '<div style="text-align:right"><a href="http://www.clicksold.com">Wordpress IDX</a> by <a href="http://www.clicksold.com"><img src="'.plugins_url('/images/cs-logo-footer.png', __FILE__).'" style="margin-left:4px;" title="Wordpress IDX" alt="Wordpress IDX"></a></div>';
	}
}

/** Redirect the user to the ClickSold "My Account" menu (with a welcome message) on the first login to the dashboard.
    If we're being hosted on the ClickSold webservers that is. **/
if( cs_is_hosted() && is_admin() ) {

	$first_login = get_option( $cs_opt_first_login, 1 ); // Set the default to TRUE as if the opt is not there then we know it's the first login.

	// If they have never logged into the back office.
	if( $first_login ) {
		// If we're not the cs plugin admin page make redirect to that page.
		if( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != 'cs_plugin_admin' ) {
			add_action('admin_init', 'first_login_redirect');
			// NOTE: The cs_plugin_admin page will record that the page has been viewed.
		}
	}
}

/** Forwards the page to the plugin activation page on first activation **/
function first_login_redirect() {
	wp_redirect(admin_url()."admin.php?page=cs_plugin_admin");
	exit();
}

/** Add the fav icon for wp installs on ClickSold servers. **/
if( cs_is_hosted() && is_admin() ) {
	add_action( 'admin_head', 'set_cs_favicon_admin' );
}

function set_cs_favicon_admin() {
	echo '<link rel="Shortcut Icon" type="image/x-icon" href="' . plugins_url( "images/favicon.ico", __FILE__ ) . '" />';
}

/** Add retrieve cs info link to login page. **/
if( cs_is_hosted() ) { // Only if it's hosed, if they have their own wp then they are on their own.
	add_action( 'login_footer', 'add_cs_info_retrieval_link_on_login_page' );
}
function add_cs_info_retrieval_link_on_login_page() {
	echo '<div style="margin: auto; width: 255px;">';
	echo '<br>';
	echo '  <a href="http://www.clicksold.com/wiki/index.php/Logging_in_to_Your_Website#Forgotten_Username" target="_blank">Forgot your ClickSold Username?</a>';
	echo '<br>';
	echo '</div>';
}

    
    

?>
