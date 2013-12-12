<?php
/*
* Class used for WordPress db-related tasks via AJAX calls
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
require_once('../../../wp-admin/includes/admin.php'); // Will setup wp correctly if the user is logged in so we can use for example the is_admin() function and expect to get a sane response.
// Sometimes plugins loaded by wordpress call ob_start() as well, this get's stacked so cleaning the buffer once is sometimes not enough. eg: NextGEN Gallery does this. We flush the buffer until it's no longer enabled.
while( ob_get_length() !== FALSE ) {
	ob_end_clean();
}

class CS_ajax_utilities {
	
	function __construct() {
		$nonce = $_GET['_csnonce'];
		
		if($nonce) {
			if(wp_verify_nonce($nonce, "cs_disable_offers_popup")) {
				$this->cs_disable_offers_popup(); 
			}
		}
		
		die();
	}
	
	function cs_disable_offers_popup() {
		if(function_exists('update_option')) {
			update_option("cs_opt_disable_offers_popup", "1");
		}
	}
}

// Create the object when called
$cs_ajax_utilities = new CS_ajax_utilities;
?>
