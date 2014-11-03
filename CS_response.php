<?php
/*
* Class used to process the response from a call to the ClickSold Server for a specific view(s)
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

class CS_response 
{

	private $response_contents = array();
	private $resource_includes; //JSON object converted to associative array
	private $response_content_type = "text/plain";
	private $response_status_code = "";
	private $plugin_section = '';
	private $error_state = false; //True when passed in an array with "cs_req_err_msg" as one of the keys

	private $current_response_section = 0; // In a multiple request <-> response scenario this holds the index of the body section that we are currently processing.

	/*Constants*/
	const body_separator = '<!--~~~ SectionID="body" ~~~-->';  //Separator tag that goes above body data
	const header_separator = '<!--~~~ SectionID="header" ~~~-->';  //Separator tag that goes above header JSON object (actually goes above header/footer JSON object)
	
	/**
	*  Object constructor - will set ClickSold hooks on creation.  Takes in one variable on object creation containing the response from the ClickSold
	*  server as a string.
	*/
	function __construct($data, $plugin_section = ''){
		$this->plugin_section = $plugin_section;
		$this->cs_parse_response($data);
		if(!$this->error_state) {
			$this->response_content_type = $data['headers']['content-type'];
			$this->response_status_code = $data['response']['code'];
			$this->set_resource_includes();
			$this->check_vars_for_requests(); // Checks the variables sent by the server to see if the server wants us to do something.
		}
	}
			
	/**
	* Splits up response into header/footer info (JSON) and module (HTML) content using a defined delimeter.
	*/
	private function cs_parse_response($response_data){
		
		// Check if the data passed in is a error message
		if(isset( $response_data[ 'cs_req_err_msg' ] )) {
			$this->response_contents[ 'body_0' ] = $response_data[ 'cs_req_err_msg' ];
			$this->error_state = true;
			return;
		}
		
		$response_body = $response_data['body'];

		// Check for any separators if there are none we assume it's an ajax call or a stack trace and just set the body from the input as is.
		if(strpos($response_body, self::header_separator) === FALSE &&
		   strpos($response_body, self::body_separator) === FALSE) {
		   
			// No tags found, just set the body.
			$this->response_contents[ 'body_0' ] = $response_body;
			return; // Ok, we have no tags, just quit right here.
		} // else - we go on to extract the segmented information.
		
		// First handle the Header and Footer section (Assuming it exists).
		if(strpos($response_body, self::header_separator) !== FALSE) {
			
			// The start of the header / footer section
			$header_footer_start = strpos($response_body, self::header_separator) + strlen(self::header_separator); // The start of the header / footer section
			$this->response_contents[ 'header_footer' ] = substr($response_body, $header_footer_start);
		}

		// Handle each of the body sections.
		$current_body_section = 0; // The int counter of the current body section. Keep track of how many we've done.
		$current_offset = 0; // The current offset into the response.
		while(strpos($response_body, self::body_separator, $current_offset) !== FALSE) { // While we have more sections in the response.

			// Skip the tag itself.
			$current_offset = strpos($response_body, self::body_separator, $current_offset) + strlen(self::body_separator);

			// Try to find the next body_separator.
			$next_separator_pos = strpos($response_body, self::body_separator, $current_offset);

			// If a body separator is not found next, then we may be at the last section in which case we check for the header_separator.
			if($next_separator_pos === FALSE) {
				$next_separator_pos = strpos($response_body, self::header_separator, $current_offset);
			}

			// If the next separtor is still not found (and we just checked for the body and header one) then we have a misformatted response as the header separator always has to follow the body ones.
			if($next_separator_pos === FALSE) {
				$this->response_contents[ 'body_' . $current_body_section ] = "Incomplete Response From Server!";
			} else { // All is well we have a next separator pos and can cut this current section out and store it.
				$this->response_contents[ 'body_' . $current_body_section ] = substr($response_body, $current_offset, ($next_separator_pos - $current_offset));
			}

			// Finish up before the next go-around.
			// NOTE: $current_offset is already past the current tag, so we'll pick up the next one automatically (if any).
			$current_body_section++;
		}

	}
	
	/**
	*  Returns the header/footer includes as a JSON object
	*/
	private function set_resource_includes(){
		//error_log($this->response_contents['header_footer']);
		
		if(!empty($this->response_contents['header_footer'])){
			$this->resource_includes = $this->my_json_decode($this->response_contents['header_footer']);
			//error_log("Size of response contents array: " . count($this->resource_includes));
			//$this->resource_includes = json_decode(trim($this->response_contents['header_footer']), true);  //See note above function my_json_decode
		}
	}
	
	/**
	 * Checks the variables sent by the server to see if the server is requesting us to do something.
	 */
	private function check_vars_for_requests() {
		
		$srv_response_vars = $this->cs_set_vars();
		
		// Process the capabilities re-synch request by setting the $cs_change_products_request flag so that the next hit on the plugin will get it to re-synchronize.
		if( !empty( $srv_response_vars['_cs_req_plugin_capabilities_resynch'] ) ) {
			global $cs_change_products_request;
			update_option( $cs_change_products_request, 1 );

			// Also flush the rules so that the pages get re-hooked up.
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}

	}
	
	
	/**
	 * Function that registers scripts/styles as well as output any block javascript/css
	 *
	 * $skip_linked - skip the linked types of includes (JS, CSS, JS_JAWR and CSS_JAWR).
	 * $skip_inline - skip the inline types of includes (JS_IN / CSS_IN and IN_RAW).
	 */
	private function cs_set_includes($block, $skip_linked = 0, $skip_inline = 0){
		global $cs_disable_script_includes_types;
		
		//Array checks used for suppressing errors due to relative links in the
		//content that reinvoke the loop
		if(!is_array($this->resource_includes)){ return; }
		if(!is_array($this->resource_includes[$block])){ return; }
		
		//error_log("cs_set_includes($block)skipLinked(".$skip_linked.")skipInline(".$skip_inline.")");
		//error_log(print_r($this->resource_includes[$block], true));
				
		foreach($this->resource_includes[$block] as $include_item) {
			
			// If the user has decided to disable some script includes using the cs_disable_script_includes($type) function we honour that here.
			if( $cs_disable_script_includes_types != null ) { // Assuming that this system has even been triggered.
				if( array_key_exists( "ALL", $cs_disable_script_includes_types ) ) { continue; }		// Special case where they want to skip all of the includes.
				if( array_key_exists( $include_item["type"], $cs_disable_script_includes_types ) ) { continue; }		// Skipping only specific types.
			}
			
			if(($include_item["type"] == "JS" || $include_item["type"] == "CSS" || $include_item["type"] == "JS_JAWR" || 
			    $include_item["type"] == "CSS_JAWR"|| $include_item["type"] == "WP_ENQ_JS"|| $include_item["type"] == "WP_ENQ_CSS") && 
				$include_item["name"] == "") { continue; } //JS, CSS, JS_JAWR, CSS_JAWR, WP_ENQ_JS & WP_ENQ_CSS types require a name field!
			
			if(($include_item["type"] == "JS" || $include_item["type"] == "JS_JAWR") && !$skip_linked) {  //Javascript File path (URL)
				$this->deregister_wp_jquery_ui($include_item["name"]);
				
				wp_deregister_script($include_item["name"]);  //Deregister just in case it's been already registered by another plugin or widget
				wp_register_script($include_item["name"], $include_item["content"]);
				wp_enqueue_script($include_item["name"]);
					
			}elseif(($include_item["type"] == "CSS" || $include_item["type"] == "CSS_JAWR" || $include_item["type"] == "CSS_IN") && !$skip_linked){     //CSS File path (URL)
				wp_deregister_style($include_item["name"]); //WARNING: function found from other sources but is not found in codex.wordpress.org..
				wp_register_style($include_item["name"], $include_item["content"]);
				wp_enqueue_style($include_item["name"]);
					
			}elseif($include_item["type"] == "JS_IN" && !$skip_inline){
				$out = $this->sanitize_output( $include_item["content"] );
				//error_log("Printing .... inline script (".$out.")");
				echo '<script type="text/javascript">/*<![CDATA[*/',$out,'/*]]>*/</script>';
				
			}elseif($include_item["type"] == "CSS_IN" && !$skip_inline){   //Page CSS
				$out = $this->sanitize_output( $include_item["content"] );
				print '<style type="text/css">'.$out.'</style>';

			}elseif($include_item["type"] == "IN_RAW" && !$skip_inline){
				$out = $this->sanitize_output( $include_item["content"] );
				print $out;
				
			}elseif($include_item["type"] == "WP_ENQ_JS"){
				if(!wp_script_is($include_item["name"], 'queue')) wp_enqueue_script($include_item["name"]);
				
			}elseif($include_item["type"] == "WP_ENQ_CSS"){
				if(!wp_style_is($include_item["name"], 'queue')) wp_enqueue_style($include_item["name"]);
			}
		}
	}
		
	/**
	 * Used for deregistering jQuery UI scripts that were previously registered by our widgets
	 * i.e. Listing Quick Search
	 */
	private function deregister_wp_jquery_ui($script_name) {
		if($script_name == 'jquery-ui'){
			// Add more as they get included by widgets... 
			// or just remove them all if it gets out of hand
			wp_deregister_script('jquery-ui-core');
			wp_deregister_script('jquery-ui-widget');
			wp_deregister_script('jquery-ui-position');
			wp_deregister_script('jquery-ui-autocomplete');
		}
	}
		
/*****************************************************************************************/

	/**
	* Returns the body content of the response (used by add_action for normal & ajax views)
	* Also used as the the_content filter function which is why it prepends it's parameter before
	* the response.
	*/
	public function get_body_contents( $org_content = "" ){
		//error_log( $org_content );
		//error_log(print_r($this->response_contents, true));
		return $org_content . $this->response_contents["body_" . $this->current_response_section];
	}
	
	/**
	 * Routines to manage the current response section that we're on.
	 */
	public function reset_response_section() {
		$this->current_response_section = 0;
	}

	public function next_response_section() {
		$this->current_response_section++;
	}

	public function get_response_section_num() {
		return $this->current_response_section;
	}
	
	public function cs_get_response_content_type(){
		return $this->response_content_type;
	}
	
	public function cs_get_response_status_code(){
		return $this->response_status_code;
	}
	
	/**
	 * Function used by add_action to populate header contents with linked includes.
	 */
	public function cs_get_header_contents_linked_only(){
		if(!empty($this->resource_includes)){
			$this->cs_set_includes("header", 0, 1); // header, skip_linked, skip_inline.
		}
	}
	
	/**
	 * Function used by add_action to populate header contents with inline includes.
	 */
	public function cs_get_header_contents_inline_only(){
		if(!empty($this->resource_includes)){
			$this->cs_set_includes("header", 1, 0); // header, skip_linked, skip_inline.
		}
	}
	
	/**
	 * Function used by add_action to populate footer contents
	 */
	public function cs_get_footer_contents(){
		if(!empty($this->resource_includes)){
			$this->cs_set_includes("footer");
		}
	}

	/**
	 * Takes any special variables set in the JSON content and places them in an associative array
	 */
	public function cs_set_vars(){
				
		//Array checks used for suppressing errors due to relative links in the
		//content that reinvoke the loop
		if(!is_array($this->resource_includes)){ return array(); }
		if(!is_array($this->resource_includes['vars'])){ return array(); }
		
		$vars = array();
		
		foreach($this->resource_includes['vars'] as $var){
			if(empty($var["name"]) || empty($var["content"])){ continue; }
			$out = $this->sanitize_output($var["content"]);
			$vars[$var["name"]] = $out;
		}
		
		return $vars;
	}
	
	/**
	*  Converts the whole response 
	*/
	public function cs_get_json(){
		return $this->my_json_decode(trim($this->get_body_contents()));
	}
	
	/**
	 * Flag that is true when the object was instantiated with an array with a CS-specific error key (from CS_request)
	 */
	public function is_error(){
		return $this->error_state;
	}
	
/*** Utility Functions *******************************************************************/
	
	/**
	 * NOTE!  Function json_decode treats escaped double quotes (\") as invalid characters.  Since we can't
	 * use single quotes either, we cannot rely on json_decode (for the time being).  This function
	 * handles escaped double quotes.
	 * Function taken from comments in http://php.net/manual/en/function.json-decode.php
	 * @param string $json json format string object
	 */
	private function my_json_decode($json){
		
		if(empty($json)) return "";
		
	    $comment = false;
	    $out = '$x=';
	    $json_length = strlen($json);
		try{
		    for ($i=0; $i<$json_length; $i++)
		    {
		        if (!$comment){
		            if (($json[$i] == '{') || ($json[$i] == '[')) { $out .= ' array('; }
		            else if (($json[$i] == '}') || ($json[$i] == ']')) { $out .= ')'; }
		            else if ($json[$i] == ':') { $out .= '=>'; }
		            else { $out .= $json[$i]; }
		        }
		        else { $out .= $json[$i]; }
		        if ($json[$i] == '"' && $json[($i-1)]!="\\") { $comment = !$comment; }
		    }
		    eval($out . ';');
		    return $x;
		}catch(Exception $e){
			error_log(print_r($e->getMessage(), true));
		}
	}
	
	/**
	 * Converts specific numeric character entities to their actual equivalents
	 * @param string $out content to be sanitized
	 */
	private function sanitize_output($out){
		
		//$sanitized = stripslashes($out);
		$sanitized = $out;
		$sanitized = str_replace("\&#59;", ";", $sanitized);
		$sanitized = str_replace("\&#34;", '"', $sanitized);
		$sanitized = str_replace("\&#39;", "'", $sanitized);
		return $sanitized;
	}	
}
?>
