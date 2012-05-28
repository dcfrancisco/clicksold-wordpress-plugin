<?php
/*
* Class used for displaying pages and handling ajax calls for the mobile site
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

include_once('../../../wp-load.php');
include_once('CS_request.php');
include_once('CS_response.php');
include_once('cs_constants.php');


class cs_mobile {

	protected $request_vars;
	protected $content_type;

	function __construct() {
		$this->request_vars = $_SERVER['QUERY_STRING'];
		if($_SERVER['REQUEST_METHOD'] == 'POST') { 
			$post_vars = http_build_query($_POST);
			if(!empty($post_vars)) $this->request_vars .= "&" . $post_vars; 
		}
		
		//If an empty call, go to mobile home page
		if(empty($this->request_vars)) {
			$this->request_vars = "pathway=458"; 
		}
	}
	
	public function get_response() {
		global $CS_SECTION_MOBILE_PARAM_CONSTANT;
		
		$cs_request = new CS_request( $this->request_vars, $CS_SECTION_MOBILE_PARAM_CONSTANT );
		$cs_response = new CS_response($cs_request->request());
		
		$this->content_type = $cs_response->cs_get_response_content_type();
		return $cs_response->get_body_contents();
	}
	
	public function get_content_type(){
		return $this->content_type;
	}
}

/* Main */
$ajax_request = new cs_mobile;
$response_body = $ajax_request->get_response();
header('Content-Type: ' . $ajax_request->get_content_type());
echo $response_body;
?>