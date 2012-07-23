<?php
/*
* Class used to process response from an AJAX call to the ClickSold Server for a specific view
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

// We start and then clean the output buffer while running the wp load in between to make our ajax requests not include
// any error text that the actual loading of wordpress may produce. This error text messes up json responses and image requests (captcha).
ob_start();
require_once('../../../wp-load.php');
ob_end_clean();

require_once('CS_request.php');
require_once('CS_response.php');
require_once('cs_constants.php');

class CS_ajax_request{
	protected $request_vars;
	protected $content_type;
	
	public $captcha;
	
	function __construct(){
		$this->request_vars = $_SERVER['QUERY_STRING'];
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			/**
			*  WARNING!!!
			*  If your post request has duplicate parameters and they don't have [] 
			*  after the parameter name then the duplicates will be parsed out.
			*/
			
			// Have to manually sanitize as array_map doesn't recognize post names with square brackets appended to them
			array_walk_recursive($_POST, array($this, 'sanitize_escaped_quotes'));
			
			$this->request_vars .= "&" . http_build_query($_POST);
		}
	}
	
	private function sanitize_escaped_quotes(&$item, $key){
		$item = stripslashes($item);
	}	
	
	/**
	 * Constructs/sends request to ClickSold server, outputs response
	 */
	public function get_response(){	
		global $CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT;
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		global $CS_SECTION_VIP_PARAM_CONSTANT;
		global $cs_change_products_request;
		
		if( $this->is_products_change_request() || $this->is_plugin_activation_request() ) update_option($cs_change_products_request, "1");
		
		$this->captcha = $this->is_captcha_request();
		
		if( $this->captcha == true ) { // A captcha request is setup differently.
			//Remove the string "captcha&" from the query string
			$this->request_vars = substr($this->request_vars , 8);
			$cs_request = new CS_request( $this->request_vars, $CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT );
			$cs_response = new CS_response( $cs_request->request( 'GET' ), $CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT );
		} else if( $this->is_admin_request() ) {	
			// *** SPECIAL CASE ***
			//If this is a listing details request, we need to have the request send in all the sections to construct the url
			if(stristr($this->request_vars, "loadListing=true") != false || stristr($this->request_vars, "loadFavoriteListings=true") != false){
				$cs_request = new CS_request( $this->request_vars, $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"], true);
			}else{
				$cs_request = new CS_request( $this->request_vars, $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
			}
			
			$cs_response = new CS_response($cs_request->request());
		} else if( $this->is_vip_request() ) {
			$cs_request = new CS_request( $this->request_vars, $CS_SECTION_VIP_PARAM_CONSTANT["wp_vip_pname"]);
			$cs_response = new CS_response($cs_request->request());
		} else if( $this->is_utils_request() ) {
			$cs_request = new CS_request( $this->request_vars, "wp_utils");
			$cs_response = new CS_response($cs_request->request());
		} else {
			$cs_request = new CS_request( $this->request_vars, null);
			$cs_response = new CS_response($cs_request->request());
		}
	
		$this->content_type = $cs_response->cs_get_response_content_type();
		return $cs_response->get_body_contents();
	}

	/**
	 * Returns true if this request is for a captcha resource (img file).
	 */
	public function is_captcha_request() {
		if(strpos($this->request_vars, 'captcha&t=') !== false) return true;
		else return false;
	}
	
	public function get_content_type(){
		return $this->content_type;
	}
	
	/**
	 * Returns true if this is a request from the admin panel (ClickSold)
	 */
	private function is_admin_request() {
		if(strpos($this->request_vars, 'wp_admin_pname') !== false) return true;
		else return false;
	}
	
	private function is_vip_request() {
		if(strpos($this->request_vars, 'wp_vip_pname') !== false) return true;
		else return false;
	}
	
	private function is_products_change_request(){
		return (strpos($this->request_vars, 'wp_products_change') !== false);
	}

	private function is_utils_request() {
		if(strpos($this->request_vars, 'wp_utils_pname') !== false) return true;
		else return false;
	}
	
	private function is_plugin_activation_request(){
		return (strpos($this->request_vars, 'wp_plugin_activate') !== false);
	}

}

	$ajax_request = new CS_ajax_request;
	$response_body = $ajax_request->get_response();
	$response_body = trim($response_body);
	
	if( $ajax_request->captcha == true ) { // Make sure that it reports itself as an image if it's an image request.
		header('Content-Type: image/jpeg');
		
		if( function_exists( 'imagecreatefromstring' ) ) { // If we have the gd module installed (provides the imagecreatefromstring function) we use that and do things properly.
			$img = imagecreatefromstring($response_body);
			imagejpeg($img, null, 100); // Also outputs the image to the browser.
			imagedestroy($img);
		} else { // Otherwise just output the image content and hope that the browser is able to make sense of it.
			echo $response_body;
		}
	} else {
		header('Content-Type: ' . $ajax_request->get_content_type());
		echo $response_body;
	}
?>
