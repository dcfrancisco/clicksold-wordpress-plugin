<?php
/**
* Class for storing utility functions
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
	
require_once(plugin_dir_path(__FILE__) . '/../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/CS_request.php');
require_once(plugin_dir_path(__FILE__) . '/CS_response.php');
include_once(plugin_dir_path(__FILE__) . '/cs_constants.php');


class CS_utilities{
	
	function __construct(){ }

//**************************** Listing Auto Blogger ****************************//
	
	public function listing_autoblog_get_listing_posts(){
		global $CS_SECTION_UTILS_PARAM_CONSTANTS;
		global $CS_VARIABLE_AUTO_BLOG_RPM_STATUS_VARS;
		global $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS;
		global $CS_VARIABLE_AUTO_BLOG_CONTENT_VARS;
		global $user_ID;
		global $cs_autoblog_new;
		global $cs_autoblog_sold;
		global $cs_autoblog_last_update;
		
		$cs_autoblog_new_opt = get_option($cs_autoblog_new);
		$cs_autoblog_sold_opt = get_option($cs_autoblog_sold);

		$req = 'pathway=7&activeBlog=' . $cs_autoblog_new_opt . '&soldBlog=' . $cs_autoblog_sold_opt;
		
		//Get listings from server
		$cs_request = new CS_request($req, $CS_SECTION_UTILS_PARAM_CONSTANTS['listing_autoblog_pname']);
		$cs_response = new CS_response($cs_request->request());
		
		// Skip processing if connection to server failed
		if($cs_response->is_error()) return;
		
		$response = $cs_response->cs_get_json();
		
		$post_queue = array();
		$now = time();
		update_option($cs_autoblog_last_update, $now);
			
		//build arguments for each listing
		foreach($response['listings'] as $listing){
			if($listing['_cs_autoblog_cs_status'] == $CS_VARIABLE_AUTO_BLOG_RPM_STATUS_VARS['Active'] && $cs_autoblog_new_opt == "1"){
				$cs_autoblog_title_opt = 'cs_autoblog_new_title';
				$cs_autoblog_cnt_opt = 'cs_autoblog_new_content';
			}else if($listing['_cs_autoblog_cs_status'] == $CS_VARIABLE_AUTO_BLOG_RPM_STATUS_VARS['Sold'] && $cs_autoblog_sold_opt == "1"){
				$cs_autoblog_title_opt = 'cs_autoblog_sold_title';
				$cs_autoblog_cnt_opt = 'cs_autoblog_sold_content';
			}else{
				continue; //Skip due to invalid status
			}
		
			$post_title_temp = get_option($cs_autoblog_title_opt);
			$post_cnt_temp = get_option($cs_autoblog_cnt_opt);
		
			if(empty($post_title_temp) || empty($post_cnt_temp)) continue;  //Skip due to blank title/content templates
			
			$post_title = $this->listing_autoblog_process_wildcards($CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS, $listing, $post_title_temp);
			$post_cnt = $this->listing_autoblog_process_wildcards($CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS, $listing, $post_cnt_temp, true);
			$post_cnt = $this->listing_autoblog_process_wildcards($CS_VARIABLE_AUTO_BLOG_CONTENT_VARS, $listing, $post_cnt, true);
			
			$post_args = array(
				'post_title' => $post_title,
				'post_content' => $post_cnt,
				'post_status' => 'publish',
				'post_date' => $listing['_cs_autoblog_date'],
				'post_author' => $user_ID,
				'post_type' => 'post',
				'post_category' => array(0)
			);
		
			array_push($post_queue, $post_args);
		}
		
		//insert the posts
		foreach($post_queue as $post){
			$error = wp_insert_post($post, true); 
			if(is_wp_error($error)){
				error_log(print_r($error, true));
			}
		}
	}
	
	/**
	*  Takes a text template, looks for strings that match the ones stored in options, and replaces them with the data in listing.
	*/
	private function listing_autoblog_process_wildcards($options, $listing, $template, $debug = false){		
		foreach($options as $key => $value){
		
			// Replace the wildcard in the template with the lookup.
			if(strpos($template, $value) !== false) { $template = str_replace($value, $listing[$key], $template); }

			/* 2012-03-30 EZ - Switched to a blind replace, I think it's less likely that people will try to use
			   a %n in the format than trying to use '%n,' or any number of diff chars that we'd then have to support.
					DEPRECATED
			//Check for wildcards
			$val_spaced = $value . " ";
			if(strpos($template, $val_spaced) !== false) { $template = str_replace($val_spaced, $listing[$key] . " ", $template); }
			
			//Check for wildcards right before line breaks
			//TODO: Ensure this works on linux - PHP_EOL may not work as it doesn't in Win
			if(strpos($template, $value . "\n") !== false) { $template = str_replace($value . "\n", $listing[$key] . "\n", $template); }
			
			//Check for wildcards surrounded by escaped quotes i.e. for uris in html tags
			if(strpos($template, "\\\"" . $value . "\\\"") !== false) { 
				$template = str_replace("\\\"" . $value . "\\\"", "\\\"" . $listing[$key] . "\\\"", $template);
			}
			
			//Check for wildcards surrounded by unescaped quotes i.e. for uris in html tags
			if(strpos($template, "\"" . $value . "\"") !== false) { 
				$template = str_replace("\"" . $value . "\"", "\"" . $listing[$key] . "\"", $template);
			}
			
			$offset = strlen($template) - strlen($value);
			
			if( ($offset >= strlen($template) || $offset >= strlen($value)) && $offset < 1 ){  //Wildcard as whole string
				if($template == $value) { $template = $listing[$key]; }
			}else if(substr_compare($template, $value, strlen($template) - strlen($value)) === false){
				// Do nuffink
			}else if(substr_compare($template, $value, strlen($template) - strlen($value)) == 0){  //End of string (not EOL)
				$template = substr_replace($template, $listing[$key], $offset, strlen($value));
			}
			*/
		}
		
		return $template;
	}
	
//****************************/Listing Auto Blogger ****************************//
}
?>