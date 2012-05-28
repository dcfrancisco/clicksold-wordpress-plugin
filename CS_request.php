<?php
/*
* Performs a request to the ClickSold.com servers
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

require_once(plugin_dir_path(__FILE__) . "cs_constants.php");

//Include the WP_Http class used for making HTTP requests
if ( !class_exists( 'WP_Http' ) ) :
include_once( ABSPATH . WPINC. '/class-http.php' );
endif;

/**
 * This class is responsible for communicating with the ClickSold plugin server.
 * @author hoangker
 */
class CS_request {

	/** Standard request vars used to identify ourselves and give info about our environment. */
	protected $pluginKey = '';
	protected $pluginNum = '';
	protected $pluginVer = '';

	/** 
	 * Multi-Request-Section support. Each request starts of with a single cs_org_req attribute
	 * calling addReqSection with a new cs_org_req will send multiple original request parameters
	 * to the plugin server using the parameter cs_org_req_n where n is 0 to the number of
	 * requests ( less 1 of course ).
	 */
	protected $cs_org_req_arr = array();
	protected $current_req_section = 0;
	const cs_org_req_param_name_prefix = "cs_org_req";

	// The instance of wp-http used to make the request.
	protected $requestObj = '';

	/** This is the pluginSection - eg: listings/ or communities/ (or whatever these page's names
            have been changed to. */
	protected $pluginSection = '';
	
	const plugin_key_opt = "cs_opt_plugin_key";
	const plugin_num_opt = "cs_opt_plugin_num";
	const plugin_ver_opt = "cs_opt_plugin_version";

	protected $production_proxy_server = 'http://wp-plugin-rpm.realpagemaker.com/';
	protected $test_proxy_server = 'http://127.0.0.201/';
	protected $test_proxy_server2 = 'http://stg-wp-plugin.office.realpagemaker.com/';	// In office staging server.
	protected $plugin_controller = 'WPPluginRpm';
	protected $plugin_admin_controller = 'WPPluginAdminRpm';
	protected $plugin_vip_controller = 'WPPluginVIPDash';
	protected $plugin_utils_controller = 'WPPluginUtilsRpm';
	protected $plugin_mobile_controller = 'WPPluginMobile';
	protected $plugin_captcha_controller = 'jcaptcha';

	protected $proxy_server = '';	// Will be selected from $production_proxy_server or $test_proxy_server in the constructor.

	// NOTE: This timeout has to be the same as the tomcat response timeout in the workers.properties file.
	protected $req_timeout = 60;	// Request timeout in seconds.

	/**
	* construct the CS_request obj.
	*/
	public function __construct( $cs_org_req, $pluginSection, $getAllSections=null ) {
		
		// The special ClickSold sections that target specific controllers.
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		global $CS_SECTION_VIP_PARAM_CONSTANT;
		global $CS_SECTION_MOBILE_PARAM_CONSTANT;
		global $CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT;
		
		// Detect which server is to be used.
		// echo "Server Address is " . $_SERVER['SERVER_ADDR'] . "<br>";
		//echo "Server Name    is " . $_SERVER['SERVER_NAME'] . "<br>";
		if($_SERVER['SERVER_NAME'] == 'localhost' || preg_match("/127.0.0./", $_SERVER['SERVER_NAME']) || preg_match("/127.0.0./", $_SERVER['SERVER_ADDR']) || preg_match("/142.244.253./", $_SERVER['SERVER_NAME'])) { // We're on localhost or 127.0.0.* ==> DEV
			//echo "DEV<br>";
			$this->proxy_server = $this->test_proxy_server;
	
		} else if(preg_match("/stg-wp.office.realpagemaker.com/", $_SERVER['SERVER_NAME'])) {
			//echo "DEV 2<br>";

		} else {
			//echo "PRODUCTION<br>";
			$this->proxy_server = $this->production_proxy_server;
		}

		// Now select the appropriate controller that will handle the request.
		if($CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"] == $pluginSection) {
			$this->proxy_server .= $this->plugin_admin_controller;
		} else if($CS_SECTION_VIP_PARAM_CONSTANT["wp_vip_pname"] == $pluginSection) {
			$this->proxy_server .= $this->plugin_vip_controller;
		} else if($CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT == $pluginSection) {
			$this->proxy_server .= $this->plugin_captcha_controller;
		} else if("wp_utils" == $pluginSection) {
			$this->proxy_server .= $this->plugin_utils_controller;
		} else if($CS_SECTION_MOBILE_PARAM_CONSTANT == $pluginSection) {
			$this->proxy_server .= $this->plugin_mobile_controller;
		} else {
			$this->proxy_server .= $this->plugin_controller;
		}

		//echo "Proxy is \"" . $this->proxy_server . "\" for plugin section (" . $pluginSection . ") $CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT <br>";

		// Set our identification parameters.
		$this->pluginKey = get_option(self::plugin_key_opt); 
		$this->pluginNum = get_option(self::plugin_num_opt);
		$this->pluginVer = get_option(self::plugin_ver_opt);
		
		//Remove the array stuff (brackets w/numbers) from the parameters
		//error_log("Request - VARS (Before): " . $cs_org_req);
		$cs_org_req = preg_replace("/\%5B(\d+)\%5D/", "", $cs_org_req);		
		$cs_org_req = preg_replace("/\[\]/", "", $cs_org_req);
		//error_log("Request - VARS (After): " . $cs_org_req);
		
		// Set the First cs_org_req.
		$this->cs_org_req_arr[ $this->current_req_section ] = $cs_org_req; // Format is n => value

		$this->pluginSection = $pluginSection;

		// Flag used for getting all 
		if(!is_null($getAllSections)) $this->getAllSections = $getAllSections;
		
		$this->requestObj = new WP_Http; // initialize the WP_Http request so we can communicate with ClickSold plugin server
	}
	
	/**
	 * Increments the current section and adds the given cs_org_request to a new request section.
	 */
	public function add_req_section( $cs_org_req ) {
		$this->current_req_section++;
		$this->cs_org_req_arr[ $this->current_req_section ] = $cs_org_req; // Format is n => value
	}

	/**
	 * Deletes the current request section and decrements the current section pointer.
	 */
	public function del_req_sec() {
		unset($this->cs_org_req_arr[ $this->current_req_section ]);
		$this->current_req_section--;
	}

	/**
	 * Returns the current number of requests (aka the size of the $this->cs_org_req_arr array).
	 */
	public function get_req_section_size() {
		return count( $this->cs_org_req_arr );
	}
	
	/**
	 * This function makes the request to the plugin server
	 */
	public function request( $method = 'POST' ){
		global $CS_SECTION_PARAM_CONSTANTS;
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		global $CS_SECTION_VIP_PARAM_CONSTANT;
		
		global $wpdb;
		$cs_posts_table = $wpdb->prefix . "cs_posts";
		$posts_table = $wpdb->prefix . "posts";

		// Form an array with all of our static parameters (used to identify the plugin and provide information about the wp environment to the plugin server).
		$parameters = array(
			"pluginKey" => $this->pluginKey,
			"pluginNum" => $this->pluginNum,
			"pluginVer" => $this->pluginVer,
			"wp_permalink_format" => get_option('permalink_structure'),
			"full_plugin_dir_url" => plugin_dir_url(__FILE__),
			"client_ip_address" => getenv("REMOTE_ADDR"),
			"wpHost" => $_SERVER['HTTP_HOST']
		);

		// Set section and page name variable, if section is available
		if(!empty($this->pluginSection)) $parameters['section'] = $this->pluginSection;
		
		// If calling from administration section, set admin param
		if($this->pluginSection == $CS_SECTION_ADMIN_PARAM_CONSTANT['wp_admin_pname']) {
			$parameters['wp_admin_pname'] = $CS_SECTION_ADMIN_PARAM_CONSTANT['wp_admin_pname'];
		}
		
		if($this->pluginSection == $CS_SECTION_VIP_PARAM_CONSTANT['wp_vip_pname']) {
			$parameters['wp_vip_pname'] = $CS_SECTION_VIP_PARAM_CONSTANT['wp_vip_pname'];
		}
		
		if($this->pluginSection != $CS_SECTION_ADMIN_PARAM_CONSTANT['wp_admin_pname'] || $this->getAllSections == true){
			// Set page specific section parameters -- these are the ones that are "available"
			$cs_posts = $wpdb->get_results( "SELECT postid, prefix FROM $cs_posts_table", OBJECT_K);
			if( "" != get_option('permalink_structure', "") ) { // We are using permalinks.
				$wp_gen_posts = $wpdb->get_results("SELECT ID, post_name FROM $posts_table WHERE ID IN(" . implode(", ", array_keys($cs_posts)) . ")", OBJECT_K);
				foreach($wp_gen_posts as $key => $value) {
					$parameters[array_search($cs_posts[$key]->prefix , $CS_SECTION_PARAM_CONSTANTS)] = $value->post_name;
				}
			} else { // If we're not using permalinks we need to translate the section names into the appropriate "page_id=###" values.
				foreach( $CS_SECTION_PARAM_CONSTANTS as $sec_param_name => $sec_param_value ) {
					foreach( $cs_posts as $cs_post ) {
						# Match up the post_id to the correct section.
						if( $cs_post->prefix == $sec_param_value ) {
							$parameters[$sec_param_name] = "?page_id=" . $cs_post->postid;
						}
					}
				}
			}
		}
		
		// Set all of the original request parameters available -- all will be processed by the plugin server in one go.
		if($method == 'POST') {
			foreach($this->cs_org_req_arr as $sec_num => $sec_cs_org_req_value){
				$parameters[ self::cs_org_req_param_name_prefix . '_' . $sec_num ] = $sec_cs_org_req_value; // Format is cs_org_req_n => org_req (where n is the section)
			}
		}else{
			parse_str($this->cs_org_req_arr[ 0 ], $parameters);
		}
		
		/** Session support... we send the old session id (if we've got it) so the plugin server does not start another one each time. **/
		$headers = array(); // Headers that will be sent as part of this request.
		
		if(!session_id()){
			session_start();
		}
		
		// If we've saved one then we have to add it to the request.
		if( isset( $_SESSION['cs_session_id'] ) ) {
			$headers[ 'Cookie' ] = $_SESSION['cs_session_id'];
		}
		
		//error_log(print_r($parameters, true));
		//echo "CS_request about to hit \"" . $this->proxy_server . "\"<br>";

		// We need to send the original user-agent string for headers that have behaviors of "conditional comments" not handled by the WordPress API
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		
		// Make the request.
		if( $method == 'POST' ) {
			$response = $this->requestObj->post( $this->proxy_server, array( 'method' => $method, 'headers' => $headers, 'body' => $parameters, 'user-agent' => $user_agent, 'timeout' => $this->req_timeout ) );
		} else { // Note we don't send the 'body' param for a get request.
			$response = $this->requestObj->get( $this->proxy_server."?".http_build_query($parameters), array( 'method' => $method, 'headers' => $headers, 'user-agent' => $user_agent, 'timeout' => $this->req_timeout ));
		}

		// Check for errors.
		if( is_wp_error( $response ) ) {
			print_r($response);
			$response = array();
		}

		// We need to save the cookie information that we got back from the server.
		if( isset( $response['headers']['set-cookie'] ) ) {
			$_SESSION['cs_session_id'] = $response['headers']['set-cookie'];
		}

		return $response;
	}
}
?>
