<?php
/**
* Various widgets for ClickSold
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

require_once(plugin_dir_path(__FILE__) . '/CS_request.php');
require_once(plugin_dir_path(__FILE__) . '/CS_response.php');
require_once(plugin_dir_path(__FILE__) . '/cs_constants.php');
global $cs_widgets_cssjs_included;
$cs_widgets_cssjs_included = false;

/**
 * Returns true if the widget name is one of the ones defined in this file. Defined UP HERE so that you remember to update
 * this list every time that a new widget is added.
 */
function cs_is_cs_widget( $name ) {
	
	if( $name == 'cs-brokerage-info-widget' )		{ return true; }
	if( $name == 'cs-widget-personal-profile' )		{ return true; }
	if( $name == 'cs-widget-idx-search' )			{ return true; }
	if( $name == 'cs-widget-mobile-site' )			{ return true; }
	if( $name == 'cs-widget-buying-info' )			{ return true; }
	if( $name == 'cs-widget-selling-info' )			{ return true; }
	if( $name == 'cs-listing-quick-search-widget' )	{ return true; }
	if( $name == 'cs-feature-listing-widget' )		{ return true; }
	if( $name == 'cs-vip-widget' )					{ return true; }
	if( $name == 'cs-idx-qs-widget' )				{ return true; }
	if( $name == 'cs-community-search-widget' )		{ return true; }

	return false;
}

/**
*  Base class for ClickSold widgets.  Contains re-usable functions for plugin functionality.
*  @author ClickSold
*/
class CS_Widget extends WP_Widget {
	
	// These cache the response that queried the plugin server for the widget scripts. This is done because the inline scripts and the linked scripts are enqueued in different handlers and we don't want to always have to hit the
	// plugin server twice in order to get them.
	protected $front_widget_scripts_cs_response;
	protected $admin_widget_scripts_cs_response;
	
	/**
	 * This constructor is ONLY used by the cs dashboard widget as it needs an instance of this class
	 * to call the get_widget_scripts routine (to enqueue the widget css).
	 */
	function CS_Widget() {
		// Does nothing.
	}

	public function fix_async_upload_image() {
		if(isset($_REQUEST['attachment_id'])) {
			$GLOBALS['post'] = get_post($_REQUEST['attachment_id']);
		}
	}
		
	/**
	 * Retrieve resized image URL
	 *
	 * @param int $id Post ID or Attachment ID
	 * @param int $width desired width of image (optional)
	 * @param int $height desired height of image (optional)
	 * @return string URL
	 * @author ClickSold
	 */
	public function get_image_url( $id, $width=false, $height=false ) {
		// Get attachment and resize but return attachment path (needs to return url)
		$attachment = wp_get_attachment_metadata( $id );
		$attachment_url = wp_get_attachment_url( $id );
		if (isset($attachment_url)) {
			if ($width && $height) {
				$uploads = wp_upload_dir();
				$imgpath = $uploads['basedir'].'/'.$attachment['file'];
				//error_log($imgpath);
				$image = image_resize( $imgpath, $width, $height );
				if ( $image && !is_wp_error( $image ) ) {
					//error_log( is_wp_error($image) );
					$image = path_join( dirname($attachment_url), basename($image) );
				} else {
					$image = $attachment_url;
				}
			} else {
				$image = $attachment_url;
			}
			if (isset($image)) {
				return $image;
			}
		}
	}
	
	/**
	 * Test context to see if the uploader is being used for the Personal Profile Widget or for other regular uploads
	 *
	 * @return void
	 * @author ClickSold
	 */
	public function is_in_widget_context() {	
		if ( isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],$this->id_base) !== false ) return true;
		elseif ( isset($_REQUEST['_wp_http_referer']) && strpos($_REQUEST['_wp_http_referer'],$this->id_base) !== false ) return true;
		elseif ( isset($_REQUEST['widget_id']) && strpos($_REQUEST['widget_id'],$this->id_base) !== false ) return true;
		
		return false;
	}
	
	/**
	 * Loads theme files in appropriate hierarchy: 1) child theme, 
	 * 2) parent template, 3) plugin resources. will look in the clicksold
	 * plugin directory in a theme and the views/ directory in the plugin
	 *
	 * @param string $custom_filter_name unique name for filter
	 * @param string $template template file to search for
	 * @return template path
	 * @author ClickSold
	 **/
	public function getTemplateHierarchy($custom_filter_name, $template) {
		// whether or not .php was added
		$template_slug = rtrim($template, '.php');
		$template = $template_slug . '.php';
		
		// get plugin folder name - may not necessarily be "clicksold"
		$plugin_folder = plugin_basename(__FILE__);
		$plugin_folder = str_ireplace("widgets.php", "", $plugin_folder);
		
		if ( $theme_file = locate_template(array($plugin_folder.$template)) ) {
			$file = $theme_file;
		} else {
			$file = 'views/' . $template;
		}
		
		return apply_filters( $custom_filter_name . $template, $file);
	}
		
	/**
	 * Loads the widget's translated strings (if any, for localization)
	 */
	public function loadPluginTextDomain() {
		load_plugin_textdomain( $this->pluginDomain, false, trailingslashit(basename(dirname(__FILE__))) . 'lang/');
	}
	
	/**
	 * DEPRECATED: Enqueue style-file, if it exists.
	 */
	function add_stylesheet() {
		$styleUrl = plugins_url('/css/' . $this->widget_stylesheet, __FILE__);
		$styleFile = plugin_dir_path(__FILE__) . '/css/' . $this->widget_stylesheet;
		
		if ( file_exists($styleFile) ) {
		    wp_register_style( $this->pluginDomain . '_stylesheet', $styleUrl );
		    wp_enqueue_style( $this->pluginDomain . '_stylesheet' );
		}
	}
	
	/**
	 * General call to enqueue script calls to the server for widget Javascript & CSS files
	 */
	public function get_widget_scripts($admin = false) {
		global $cs_widgets_cssjs_included;
		
		if($cs_widgets_cssjs_included === true) return;
		else $cs_widgets_cssjs_included = true;
		
		$cs_response = null;
		
		// Enqueue CSS & JS libs
		if($admin === true) {
			add_action('admin_enqueue_scripts', array($this, 'get_admin_widget_scripts'));	
			add_action('admin_head', array($this, 'get_admin_widget_inline_scripts'));
		} else {
			add_action('wp_enqueue_scripts', array($this, 'get_front_widget_scripts'));
			add_action('wp_head', array($this, 'get_front_widget_inline_scripts'));
		}
	}
	
	/**
	 *  Retrieves Javascript / CSS scripts used in widgets.php
	 */
	public function get_front_widget_scripts() {
		
		// If we have not yet queried for the widget scripts let's do so now.
		if( !isset( $this->front_widget_scripts_cs_response ) ) {
			$cs_request = new CS_request("pathway=590", null);
			$this->front_widget_scripts_cs_response = new CS_response($cs_request->request());
		}
		if(!$this->front_widget_scripts_cs_response->is_error()) $this->front_widget_scripts_cs_response->cs_get_header_contents_linked_only();
	}
	
	/**
	 *  Retrieves inline Javascript for widgets
	 */
	public function get_front_widget_inline_scripts() {

		// If we have not yet queried for the widget scripts let's do so now.
		if( !isset( $this->front_widget_scripts_cs_response ) ) {
			$cs_request = new CS_request("pathway=590", null);
			$this->front_widget_scripts_cs_response = new CS_response($cs_request->request());
		}
		if(!$this->front_widget_scripts_cs_response->is_error()) $this->front_widget_scripts_cs_response->cs_get_header_contents_inline_only();
	}
	
	/**
	 *  Retrieves Javascript / CSS scripts used in widgets.php called in admin area
	 */
	public function get_admin_widget_scripts(){
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;

		// If we have not yet queried for the widget scripts let's do so now.
		if( !isset( $this->admin_widget_scripts_cs_response ) ) {
			$cs_request = new CS_request("pathway=591", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
			$this->admin_widget_scripts_cs_response = new CS_response($cs_request->request());
		}
		if(!$this->admin_widget_scripts_cs_response->is_error()) $this->admin_widget_scripts_cs_response->cs_get_header_contents_linked_only();
	}
	
	/**
	 *  Retrieves inline Javascript for widgets called in admin area
	 */
	public function get_admin_widget_inline_scripts(){
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;

		// If we have not yet queried for the widget scripts let's do so now.
		if( !isset( $this->admin_widget_scripts_cs_response ) ) {
			$cs_request = new CS_request("pathway=591", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
			$this->admin_widget_scripts_cs_response = new CS_response($cs_request->request());
		}
		if(!$this->admin_widget_scripts_cs_response->is_error()) $this->admin_widget_scripts_cs_response->cs_get_header_contents_inline_only();
	}
	
	/**
	 * Calls wp_enqueue_media() 
	 */
	public function call_admin_media_upload_files(){
		wp_enqueue_media();
	}	
	
	/**
	 * Turns off usage of the WP 3.5 media uploader if returns false
	 */
	private function activate_new_media_upload() {
		return true;
	}
	
	/**
	 *  Returns boolean on whether or not the WP version is 3.5 or higher
	 */
	public function use_new_media_upload() {
		global $wp_version;
		if($this->activate_new_media_upload() == false) return false;
		return version_compare($wp_version, '3.5', '>=');
	}
	
	/**
	 *  Initializes media upload functionality
	 */
	public function init_media_upload() {
		global $wp_version;
		if ( $this->activate_new_media_upload() == false || version_compare($wp_version, '3.5', '<') )
			$this->init_old_media_upload();
		else
			$this->init_new_media_upload();
	}
	
/** Old Media Upload **/
	
	public function init_old_media_upload() {
		global $pagenow;
		add_action( 'admin_init', array( $this, 'fix_async_upload_image' ) );
		if ( 'widgets.php' == $pagenow ) {
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_script( 'thickbox' );
			$this->get_widget_scripts(true);
		} else if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
			add_filter( 'image_send_to_editor', array( $this,'image_send_to_editor'), 1, 8 );
			add_filter( 'gettext', array( $this, 'replace_text_in_thickbox' ), 1, 3 );
			add_filter( 'media_upload_tabs', array( $this, 'media_upload_tabs' ) );
		}
	}
	
	/**
	 * Filter image_end_to_editor results
	 *
	 * @param string $html 
	 * @param int $id 
	 * @param string $alt 
	 * @param string $title 
	 * @param string $align 
	 * @param string $url 
	 * @param array $size 
	 * @return string javascript array of attachment url and id or just the url 
	 * @author ClickSold 
	 */
	public function image_send_to_editor( $html, $id, $caption, $title, $align, $url, $size, $alt = '' ) {
		// Normally, media uploader return an HTML string (in this case, typically a complete image tag surrounded by a caption).
		// Don't change that; instead, send custom javascript variables back to opener.
		// Check that this is for the widget. Shouldn't hurt anything if it runs, but let's do it needlessly.
		if ( $this->is_in_widget_context() ) {
			if ($alt=='') $alt = $title;
			?>
			<script type="text/javascript">
				// send image variables back to opener
				var win = window.dialogArguments || opener || parent || top;
				win.IW_html = '<?php echo addslashes($html) ?>';
				win.IW_img_id = '<?php echo $id ?>';
				win.IW_size = '<?php echo $size ?>';
			</script>
			<?php
		}
		return $html;
	}
	
	/**
	 * Somewhat hacky way of replacing "Insert into Post" with "Insert into Widget"
	 *
	 * @param string $translated_text text that has already been translated (normally passed straight through)
	 * @param string $source_text text as it is in the code
	 * @param string $domain domain of the text aka $this->pluginDomain
	 * @return void
	 * @author ClickSold
	 */
	public function replace_text_in_thickbox($translated_text, $source_text, $domain) {	
		if ( $this->is_in_widget_context() ) {
			if ('Insert into Post' == $source_text) {
				return __('Insert Into Widget', $this->pluginDomain );
			}
		}
		return $translated_text;
	}
	
	/**
	 * Remove from url tab until that functionality is added to widgets.
	 *
	 * @param array $tabs 
	 * @return void
	 * @author ClickSold
	 */
	public function media_upload_tabs($tabs) {
		if ( $this->is_in_widget_context() ) {
			unset($tabs['type_url']);
		}
		return $tabs;
	}
	
/** New Media Upload **/
	public function init_new_media_upload() {
		global $pagenow;
		if ( 'widgets.php' == $pagenow ) {
			add_action('admin_enqueue_scripts', array($this, 'call_admin_media_upload_files'));
			$this->get_widget_scripts(true);
		}
	}
}

/**
 * Personal Profile Widget class
 *
 * @author ClickSold
 **/
class Personal_Profile_Widget extends CS_Widget {
	
	/**
	 * Personal Profile Widget constructor
	 *
	 * @return void
	 * @author ClickSold
	 */
	function Personal_Profile_Widget() {
		$this->pluginDomain = 'personal_profile_widget';
		$this->loadPluginTextDomain();
		$widget_ops = array( 'classname' => 'cs-widget-personal-profile', 'description' => __( 'Add your profile photo and contact information to your website.', $this->pluginDomain ) );
		$control_ops = array( 'id_base' => 'cs-widget-personal-profile' );
		$this->WP_Widget('cs-widget-personal-profile', __('ClickSold Profile Widget', $this->pluginDomain), $widget_ops, $control_ops);
		
		if ( defined("WP_ADMIN") && WP_ADMIN ) {
    		$this->init_media_upload();
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) ) {
			$this->get_widget_scripts(false);
		}
	}

	/**
	 * Widget frontend output
	 *
	 * @param array $args 
	 * @param array $instance 
	 * @return void
	 * @author ClickSold
	 */
	function widget( $args, $instance ) {
		extract( $args );
		extract( $instance );
		$title = apply_filters( 'widget_title', empty( $title ) ? '' : $title );
		include( $this->getTemplateHierarchy( 'cs_template_personal-profile-widget_', 'personal-profile-widget' ) );
	}

	/**
	 * Update widget options
	 *
	 * @param object $new_instance Widget Instance
	 * @param object $old_instance Widget Instance 
	 * @return object
	 * @author ClickSold
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		if ( isset($new_instance['description']) ) {
			if ( current_user_can('unfiltered_html') ) {
				$instance['description'] = $new_instance['description'];
			} else {
				$instance['description'] = wp_filter_post_kses($new_instance['description']);
			}
		}
		$instance['link'] = $new_instance['link'];
		$instance['width'] = $new_instance['width'];
		$instance['height'] = $new_instance['height'];
		$instance['image'] = $new_instance['image'];
		$instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
		if( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
			$instance['imageurl'] = str_replace('http://', 'https://', $instance['imageurl']);
		}
		$instance['phone'] = $new_instance['phone'];
		$instance['mobilePhone'] = $new_instance['mobilePhone'];
		$instance['fax'] = $new_instance['fax'];
		$instance['email'] = $new_instance['email'];
		
		$instance['showIcons'] = !empty($new_instance['showIcons']) ? 1 : 0;
		return $instance;
	}

	/**
	 * Form UI
	 *
	 * @param object $instance Widget Instance
	 * @return void
	 * @author ClickSold
	 */
	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 
			'title' => '', 
			'description' => '', 
			'link' => '', 
			'width' => '', 
			'height' => '', 
			'image' => '',
			'imageurl' => '',
			'phone' => '',
			'mobilePhone' => '',
			'fax' => '',
			'email' => '',
			'showIcons' => ''
		) );
		$showIcons = isset( $instance['showIcons'] ) ? (bool) $instance['showIcons'] : false;
		include( $this->getTemplateHierarchy( 'cs_template_personal-profile-widget_', 'personal-profile-widget-admin' ) );
	}
	
}

/**
 * Brokerage Info Widget Class
 * @author ClickSold
 */
class Brokerage_Info_Widget extends CS_Widget {

	private $PLUGIN_NAME = 'ClickSold Brokerage Info Widget';
	private $PLUGIN_SLUG = 'cs-brokerage-info-widget';
	private $PLUGIN_CLASSNAME = 'widget_brokerage_info';
	private $PLUGIN_BROK_LOGOS = array();
	private $PLUGIN_DEFAULTS = array (
		'name' => '',
		'logo_src' => '',
		'upload_logo_src' => '',
		'addr' => '',
		'phone' => '',
		'fax' => '',
		'email' => '',
		'web' => '',
		'text' => ''
	);
	
	function Brokerage_Info_Widget() {
	
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		
		$widget_opts = array (
			'classname' => $this->PLUGIN_CLASSNAME, 
			'description' => 'Widget containing user\'s brokerage information'
		);	
		
		$this->WP_Widget($this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts);
		
		// Load JavaScript and Stylesheets
		if ( defined("WP_ADMIN") && WP_ADMIN ) {
			global $pagenow;
			if( 'widgets.php' == $pagenow ) $this->get_brokerage_logos();  //Load array of brokerage logos from server
    		$this->init_media_upload();
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget($args, $instance) {
		extract($args, EXTR_SKIP);
		echo $before_widget;
		
		include( $this->getTemplateHierarchy( 'cs_template_brokerage-info-widget_', 'brokerage-info-widget' ) );
		
		echo $after_widget;
	}
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['name'] = $new_instance['name'];
		$instance['logo_src'] = $new_instance['logo_src'];
		$instance['upload_logo_src'] = $new_instance['upload_logo_src'];
		$instance['addr'] = $new_instance['addr'];
		$instance['phone'] = $new_instance['phone'];
		$instance['fax'] = $new_instance['fax'];
		$instance['email'] = $new_instance['email'];
		$instance['web'] = $new_instance['web'];
		$instance['text'] = $new_instance['text'];
		
		return $instance;
	}
	
	function form($instance) {
		global $PLUGIN_DEFAULTS;
		global $PLUGIN_BROK_LOGOS;
		
		if(empty($PLUGIN_BROK_LOGOS)) $this->get_brokerage_logos();  //Will always run after form submit
		
		// Get list of brokerages and associated logos from server
		$brok_logos = $PLUGIN_BROK_LOGOS;
		
		$this->PLUGIN_DEFAULTS['logo_src'] = $brok_logos[1]["src"];		
		$instance = wp_parse_args((array) $instance, $this->PLUGIN_DEFAULTS);
		
		include( $this->getTemplateHierarchy( 'cs_template_brokerage-info-widget_', 'brokerage-info-widget-admin' ) );
		
	}
		
	/*--------------------------------------------------*/
	/* Private Functions
	/*--------------------------------------------------*/
	
	/**
	 *  Queries the server for a list of available brokerage logos
	 */
	private function get_brokerage_logos(){
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		global $PLUGIN_BROK_LOGOS;
		
		$cs_request = new CS_request("pathway=562", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return;
		$json_response = $cs_response->cs_get_json();
		
		$PLUGIN_BROK_LOGOS = $json_response['brok_images'];
	}
	
} // end class

/**
 * IDX Search Widget Class
 * @author ClickSold
 */
class IDX_Search_Widget extends CS_Widget {
	
	private $default_img_url = null;
	private $PLUGIN_NAME = 'ClickSold MLS&reg; Search Widget';
	private $PLUGIN_SLUG = 'cs-widget-idx-search';
	private $PLUGIN_CLASSNAME = 'cs-widget-idx-search';
	private $PLUGIN_DOMAIN = 'cs-widget-idx-search';

	
	/**
	 * IDX Search Widget constructor
	 *
	 * @return void
	 * @author ClickSold
	 */
	function IDX_Search_Widget() {
		
		global $default_img_url;
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		global $PLUGIN_DOMAIN;
		
		$this->pluginDomain = $PLUGIN_DOMAIN;
		$this->default_img_url = plugins_url('images/widget-idx.png', __FILE__);
	
		$this->loadPluginTextDomain();
		$widget_ops = array( 
			'classname' => $this->PLUGIN_CLASSNAME, 
			'description' => 'Add a link to the MLS&reg; Search page in your website\'s widget bar.' 
		);
		
		$this->WP_Widget($this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_ops);

		if ( defined("WP_ADMIN") && WP_ADMIN ) {
    		$this->init_media_upload();
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) ) {
			$this->get_widget_scripts(false);
		}
	}

	/**
	 * Widget frontend output
	 *
	 * @param array $args 
	 * @param array $instance 
	 * @return void
	 * @author ClickSold
	 */
	function widget( $args, $instance ) {
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_idx-search-widget_', 'idx-search-widget' ) );
	}

	/**
	 * Update widget options
	 *
	 * @param object $new_instance Widget Instance
	 * @param object $old_instance Widget Instance 
	 * @return object
	 * @author ClickSold
	 */
	function update( $new_instance, $old_instance ) {
		$instance['imagetype'] = $new_instance['imagetype'];
		$instance['image'] = $new_instance['image'];
		$instance['link'] = $new_instance['link'];
		$instance['alt_text'] = $new_instance['alt_text'];
		$instance['smallText'] = $new_instance['smallText'];
		$instance['largeText'] = $new_instance['largeText'];
		
		if($new_instance['imagetype'] == "custom") {
			$instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
		} else { 
			$instance['imageurl'] = $new_instance['image'];
		}
		
		if( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
			$instance['imageurl'] = str_replace('http://', 'https://', $instance['imageurl']);
		}

		return $instance;
	}

	/**
	 * Form UI
	 *
	 * @param object $instance Widget Instance
	 * @return void
	 * @author ClickSold
	 */
	function form( $instance ) {
		global $wpdb;
		global $wp_rewrite;
		
		$url = '#';
		
		//Get IDX page link, if exists...
		$idx_page = $wpdb->get_row("SELECT post_name, guid FROM " . $wpdb->posts . " WHERE post_name like '%mls%' AND post_name like '%search%' AND post_type = 'page' AND post_status != 'trash'");
		if(!is_null($idx_page)) {
			if($wp_rewrite->using_permalinks()) 
				$url = '/' . $idx_page->post_name;
			else 
				$url = $idx_page->guid;
		}

		global $default_img_url;
		$instance = wp_parse_args((array) $instance, array(
			'imagetype' => 'default', 
			'image' => $this->default_img_url, 
			'link' => $url,
			'alt_text' => 'MLS&reg; Search', 
			'smallText' => 'Find All Listings on a',
			'largeText' => 'Map-Based Search',
			'imageurl' => $this->default_img_url
		));
				
		include( $this->getTemplateHierarchy( 'cs_template_idx-search-widget_', 'idx-search-widget-admin' ) );
	}
	
}

/**
 * Mobile Site Widget Class
 * @author ClickSold
 */
class Mobile_Site_Widget extends CS_Widget {

	private $default_img_url = null;
	private $PLUGIN_NAME = 'ClickSold Mobile Site Widget';
	private $PLUGIN_SLUG = 'cs-widget-mobile-site';
	private $PLUGIN_CLASSNAME = 'cs-widget-mobile-site';
	private $PLUGIN_DOMAIN = 'cs-widget-mobile-site';
	
	/**
	 * Mobile Site Widget constructor
	 *
	 * @return void
	 * @author ClickSold
	 */
	function Mobile_Site_Widget() {
	
		global $default_img_url;
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		global $PLUGIN_DOMAIN;
		
		$this->pluginDomain = $PLUGIN_DOMAIN;
		$this->mobileSiteUrl = plugins_url( "cs_mobile.php", __FILE__);
		$this->default_img_url = plugins_url('images/widget-mobile.png', __FILE__);
		
		$this->loadPluginTextDomain();
		$widget_ops = array( 
			'classname' => $this->PLUGIN_CLASSNAME, 
			'description' => 'Add a link to your ClickSold mobile site in your website\'s widget bar.' 
		);
		
		$this->WP_Widget($this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_ops);

		if ( defined("WP_ADMIN") && WP_ADMIN ) {
    		$this->init_media_upload();
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) ) {
			$this->get_widget_scripts(false);
		}
	}

	/**
	 * Widget frontend output
	 *
	 * @param array $args 
	 * @param array $instance 
	 * @return void
	 * @author ClickSold
	 */
	function widget( $args, $instance ) {
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_mobile-site-widget_', 'mobile-site-widget' ) );
	}

	/**
	 * Update widget options
	 *
	 * @param object $new_instance Widget Instance
	 * @param object $old_instance Widget Instance 
	 * @return object
	 * @author ClickSold
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['imagetype'] = $new_instance['imagetype'];
		$instance['image'] = $new_instance['image'];
		$instance['alt_text'] = $new_instance['alt_text'];
		$instance['smallText'] = $new_instance['smallText'];
		$instance['largeText'] = $new_instance['largeText'];
		
		if($new_instance['imagetype'] == "custom") {
			$instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
		} else {
			$instance['imageurl'] = $new_instance['image'];
		}
		
		if( isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ) $instance['imageurl'] = str_replace('http://', 'https://', $instance['imageurl']);		
		
		return $instance;
	}

	/**
	 * Form UI
	 *
	 * @param object $instance Widget Instance
	 * @return void
	 * @author ClickSold
	 */
	function form( $instance ) {
		$instance = wp_parse_args((array) $instance, array(
			'imagetype' => 'default', 
			'image' => $this->default_img_url, 
			'alt_text' => 'Mobile Version', 
			'smallText' => 'Search Real Estate on Your',
			'largeText' => 'Mobile Device',
			'imageurl' => $this->default_img_url
		));
		include( $this->getTemplateHierarchy( 'cs_template_mobile-site-widget_', 'mobile-site-widget-admin' ) );
	}
}

/**
 * Buying Information Widget Class
 * @author ClickSold
 */
class Buying_Info_Widget extends CS_Widget{

	private $default_img_url = null;
	private $PLUGIN_NAME = 'ClickSold Buying Info Widget';
	private $PLUGIN_SLUG = 'cs-widget-buying-info';
	private $PLUGIN_CLASSNAME = 'cs-widget-buying-info';
	private $PLUGIN_DOMAIN = 'cs-widget-buying-info';
	
	function Buying_Info_Widget(){
	
		global $default_img_url;
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		global $PLUGIN_DOMAIN;
		
		$this->pluginDomain = $PLUGIN_DOMAIN;
		$this->default_img_url = plugins_url('images/widget-house.png', __FILE__);
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Add a section for property buying information in your website\'s widget bar.'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		if ( defined("WP_ADMIN") && WP_ADMIN ) {
    		$this->init_media_upload();
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget( $args, $instance ){
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_buying-info-widget_', 'buying-info-widget' ) );
	}
	
	function update( $new_instance, $old_instance ){
		$instance['imagetype'] = $new_instance['imagetype'];
		$instance['image'] = $new_instance['image'];
		$instance['link'] = $new_instance['link'];
		$instance['alt_text'] = $new_instance['alt_text'];
		$instance['smallText'] = $new_instance['smallText'];
		$instance['largeText'] = $new_instance['largeText'];
		
		if($new_instance['imagetype'] == "custom") $instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
		else $instance['imageurl'] = $new_instance['image'];
		
		if( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
			$instance['imageurl'] = str_replace('http://', 'https://', $instance['imageurl']);		
		}
		
		return $instance;
	}
	
	function form( $instance ){
		global $wpdb;
		global $wp_rewrite;
		
		$url = '#';
		
		//Get buying page link, if exists...
		$buying_page = $wpdb->get_row("SELECT post_name, guid FROM " . $wpdb->posts . " WHERE post_name = 'buying' AND post_type = 'page' AND post_status != 'trash'");
		if(!is_null($buying_page)) {
			if($wp_rewrite->using_permalinks()) 
				$url = '/' . $buying_page->post_name;
			else 
				$url = $buying_page->guid;
		}
	
		$instance = wp_parse_args((array) $instance, array(
			'imagetype' => 'default', 
			'image' => $this->default_img_url, 
			'link' => $url,
			'alt_text' => 'Buying Information', 
			'smallText' => 'Get Critical Information on',
			'largeText' => 'Buying Real Estate',
			'imageurl' => $this->default_img_url
		));
		include( $this->getTemplateHierarchy( 'cs_template_buying-info-widget_', 'buying-info-widget-admin' ) );
	}
	
}

/**
 * Selling Information Widget Class
 * @author ClickSold
 */
class Selling_Info_Widget extends CS_Widget{

	private $default_img_url = null;
	private $PLUGIN_NAME = 'ClickSold Selling Info Widget';
	private $PLUGIN_SLUG = 'cs-widget-selling-info';
	private $PLUGIN_CLASSNAME = 'cs-widget-selling-info';
	private $PLUGIN_DOMAIN = 'cs-widget-selling-info';

	function Selling_Info_Widget(){
	
		global $default_img_url;
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		global $PLUGIN_DOMAIN;
		
		$this->pluginDomain = $PLUGIN_DOMAIN;
		$this->default_img_url = plugins_url('images/widget-forsale.png', __FILE__);
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Add a section for property selling information in your website\'s widget bar.'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		if ( defined("WP_ADMIN") && WP_ADMIN ) {
    		$this->init_media_upload();
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget( $args, $instance ){
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_selling-info-widget_', 'selling-info-widget' ) );
	}
	
	function update( $new_instance, $old_instance ){
		$instance['imagetype'] = $new_instance['imagetype'];
		$instance['image'] = $new_instance['image'];
		$instance['link'] = $new_instance['link'];
		$instance['alt_text'] = $new_instance['alt_text'];
		$instance['smallText'] = $new_instance['smallText'];
		$instance['largeText'] = $new_instance['largeText'];
		
		if($new_instance['imagetype'] == "custom") $instance['imageurl'] = $this->get_image_url($new_instance['image'],$new_instance['width'],$new_instance['height']);  // image resizing not working right now
		else $instance['imageurl'] = $new_instance['image'];
		
		if( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
			$instance['imageurl'] = str_replace('http://', 'https://', $instance['imageurl']);		
		}
		
		return $instance;
	}
	
	function form( $instance ){
		global $wpdb;
		global $wp_rewrite;
		
		$url = '#';
		
		//Get selling page link, if exists...
		$selling_page = $wpdb->get_row("SELECT post_name, guid FROM " . $wpdb->posts . " WHERE post_name = 'selling' AND post_type = 'page' AND post_status != 'trash'");
		if(!is_null($selling_page)) {
			if($wp_rewrite->using_permalinks()) 
				$url = '/' . $selling_page->post_name;
			else 
				$url = $selling_page->guid;
		}
	
		$instance = wp_parse_args((array) $instance, array(
			'imagetype' => 'default', 
			'image' => $this->default_img_url, 
			'link' => $url,
			'alt_text' => 'Selling Information', 
			'smallText' => 'Get Critical Information on',
			'largeText' => 'Selling Real Estate',
			'imageurl' => $this->default_img_url
		));

		include( $this->getTemplateHierarchy( 'cs_template_selling-info-widget_', 'selling-info-widget-admin' ) );
	}
	
}

/**
 * Listing Quick Search Widget Class
 * @author ClickSold
 */
class Listing_QS_Widget extends CS_Widget{
	
	private $PLUGIN_NAME = 'ClickSold Listing Quick Search Widget';
	private $PLUGIN_SLUG = 'cs-listing-quick-search-widget';
	private $PLUGIN_CLASSNAME = 'widget-listing-quick-search';

	function Listing_QS_Widget(){
	
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		
		$this->pluginDomain = 'listing_quick_search_widget';
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Add a section for a text-based listing search in your website\'s widget bar.'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		global $pagenow;
		
		// Add scripts for site usage
		if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) && !wp_script_is($this->PLUGIN_SLUG . '-js') ) {
			
			//NOTE: If we avoid using base_header on our script calls, we will need to use the scripts below
			//wp_enqueue_script('jquery');
			//wp_enqueue_script('jquery-ui-core');
			//wp_enqueue_script('jquery-ui-position');
			//wp_enqueue_script('jquery-ui-autocomplete');
			
			$this->get_widget_scripts(false);
		}
	}
		
	function widget( $args, $instance ){
		global $wpdb;
		global $wp_rewrite;
		global $CS_GENERATED_PAGE_PARAM_CONSTANTS;
				
		$table_name = $wpdb->prefix . "cs_posts";
		
		//Get the ids of the idx and listings pages
		$pages = $wpdb->get_results("SELECT postid, parameter FROM " . $table_name . " WHERE parameter IN('" . $CS_GENERATED_PAGE_PARAM_CONSTANTS["listings"] . "', '" . $CS_GENERATED_PAGE_PARAM_CONSTANTS["idx"] . "', '" . $CS_GENERATED_PAGE_PARAM_CONSTANTS["community"] . "')");
		
		$idx_id = null;
		$listings_id = null;
		$comm_id = null;
		
		foreach($pages as $page) {
			if($page->parameter == $CS_GENERATED_PAGE_PARAM_CONSTANTS["idx"] ) $idx_id = $page->postid;
			else if($page->parameter == $CS_GENERATED_PAGE_PARAM_CONSTANTS["listings"]) $listings_id = $page->postid;
			else if($page->parameter == $CS_GENERATED_PAGE_PARAM_CONSTANTS["community"]) $comm_id = $page->postid;
		}
		
		if(is_null($idx_id) || is_null($listings_id) || is_null($comm_id)) return;
		
		$using_permalinks = $wp_rewrite->using_permalinks();
		
		// Get the pathname or query string of those pages
		if( $using_permalinks ) {
			$idx_url = $wpdb->get_var("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = " . $idx_id . " AND post_type = 'page' AND post_status != 'trash'");
			$listings_url = $wpdb->get_var("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = " . $listings_id . " AND post_type = 'page' AND post_status != 'trash'");
			$comm_url = $wpdb->get_var("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = " . $comm_id . " AND post_type = 'page' AND post_status != 'trash'");
		} else {
			$using_permalinks = 0;
			
			$idx_url = $wpdb->get_var("SELECT guid FROM " . $wpdb->posts . " WHERE ID = " . $idx_id . " AND post_type = 'page' AND post_status != 'trash'");
			$listings_url = $wpdb->get_var("SELECT guid FROM " . $wpdb->posts . " WHERE ID = " . $listings_id . " AND post_type = 'page' AND post_status != 'trash'");
			$comm_url = $wpdb->get_var("SELECT guid FROM " . $wpdb->posts . " WHERE ID = " . $comm_id . " AND post_type = 'page' AND post_status != 'trash'");
			
			//Strip the root url
			$patt = "/\/\?/";
			$idx_url_parts = preg_split($patt, $idx_url);
			$listings_url_parts = preg_split($patt, $listings_url);
			$comm_url_parts = preg_split($patt, $comm_url);
			
			//Check if the guid is valid
			if(count($idx_url_parts) < 2 || count($listings_url_parts) < 2 || count($comm_url_parts) < 2) return;
			
			$idx_url = "?" . $idx_url_parts[1];
			$listings_url = "?" . $listings_url_parts[1];
			$comm_url = "?" . $comm_url_parts[1];
		}
				
		if(is_null($idx_url) || is_null($listings_url) || is_null($comm_url)) return;
		
		if($wp_rewrite->using_permalinks()) {
			$idx_url .= '/?term=';
			$listings_url .= '/';
			$comm_url .= '/';
		} else {
			$idx_url .= '&term=';
			$listings_url .= '&mlsNum='; 
			$comm_url .= '&city=#&neigh=#'; //Note: the js will fill in the "neigh" query param
		}
		
		$widgetStyles = "";
		$formContainerStyles = "";
		$backgroundStyles = "";
		$vertStyles = "";
		
		if(!empty($instance['widgetHeight'])) { $widgetStyles = ' style="height:'.$instance['widgetHeight'].';"'; }
		
		if(!empty($instance['minWidth'])) { $formContainerStyles .= 'min-width:'.$instance['minWidth'].';'; }
		if(!empty($instance['maxWidth'])) { $formContainerStyles .= 'max-width:'.$instance['maxWidth'].';'; }
		if(!empty($instance['minHeight'])) { $formContainerStyles .= 'min-height:'.$instance['minHeight'].';'; }
		if(!empty($instance['maxHeight'])) { $formContainerStyles .= 'max-height:'.$instance['maxHeight'].';'; }
		
		if(!empty($instance['backgroundColor'])) { 
			if(substr_compare($instance['backgroundColor'], "rgba", 0, 4, true) === 0) {
				$backgroundStyles = $instance['backgroundColor'];
			} else if(substr_compare($instance['backgroundColor'], "rgb", 0, 3, true) === 0) {
				preg_match('/\((.*?)\)/', $instance['backgroundColor'], $rgb);
				if(!empty($instance['backgroundOpacity']) && count($rgb) == 3) {
					$backgroundStyles = 'background:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', '.$instance['backgroundOpacity'].');'; 
				} else {
					$backgroundStyles = 'background:'.$instance['backgroundColor'].';';
				}
			} else if(substr_compare($instance['backgroundColor'], "#", 0, 1) === 0) {
				if(!empty($instance['backgroundOpacity'])) {
					$color = ltrim($instance['backgroundColor'], "#");
					if(strlen($color) == 6) {
						$color_parts = str_split($color, 2);
						$rgb = array_map('hexdec', $color_parts);
						$backgroundStyles = 'background:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', '.$instance['backgroundOpacity'].');'; 
					} else if(strlen($color) == 3) {
						$color_parts = str_split($color);
						$hex = array($color_parts[0].$color_parts[0], $color_parts[1].$color_parts[1], $color_parts[2].$color_parts[2]);
						$rgb = array_map('hexdec', $hex);
						$backgroundStyles = 'background:rgba('.$rgb[0].', '.$rgb[1].', '.$rgb[2].', '.$instance['backgroundOpacity'].');'; 
					} else {
						$backgroundStyles = 'background-color:'.$instance['backgroundColor'].';';
					}
				} else {
					$backgroundStyles = 'background-color:'.$instance['backgroundColor'].';';
				}
			}
		}
		
		if(!empty($instance['top'])) {
			$vertStyles .= 'position:relative;top:'.$instance['top'].';';
		}
		
		if(!empty($instance['translateY'])) {
			$vertStyles .= 'transform:translateY('.$instance['translateY'].');-webkit-transform:translateY('.$instance['translateY'].');-ms-transform:translateY('.$instance['translateY'].');';
			//$vertStyles .= 'transform-style:preserve-3d;-webkit-transform-style:preserve-3d;-moz-transform-style:preserve-3d;';
		}
		
		if(!empty($formContainerStyles) || !empty($backgroundStyles) || !empty($vertStyles)) {
			$formContainerStyles = ' style="' . $vertStyles . $formContainerStyles . $backgroundStyles . '"'; 
		}
		
		extract( $args );
		extract( $instance );
		
		include( $this->getTemplateHierarchy( 'cs_template_listing-quick-search-widget_', 'listing-quick-search-widget' ) );
	}
	
	function update( $new_instance, $old_instance ){
		$instance['title'] = $new_instance['title'];
		$instance['showMLSSearchLogo'] = !empty($new_instance['showMLSSearchLogo']) ? 1 : 0;
		$instance['showSearchButton'] = !empty($new_instance['showSearchButton']) ? 1 : 0;
		$instance['backgroundColor'] = $new_instance['backgroundColor'];
		$instance['backgroundOpacity'] = $new_instance['backgroundOpacity'];
		$instance['minWidth'] = $new_instance['minWidth'];
		$instance['maxWidth'] = $new_instance['maxWidth'];
		$instance['minHeight'] = $new_instance['minHeight'];
		$instance['maxHeight'] = $new_instance['maxHeight'];
		$instance['translateY'] = $new_instance['translateY'];
		$instance['widgetHeight'] = $new_instance['widgetHeight'];
		$instance['top'] = $new_instance['top'];
		return $instance;
	}
	
	function form( $instance ){	
		$instance_opts = array( 
			'title' => '',
			'showMLSSearchLogo' => '',
			'showSearchButton' => '',
			'backgroundColor' => '',
			'backgroundOpacity' => '',
			'minWidth' => '',
			'maxWidth' => '',
			'minHeight' => '',
			'maxHeight' => '',
			'translateY' => '-50',
			'widgetHeight' => '',
			'top' => '50%'
		);
	
		$showMLSSearchLogo = isset( $instance['showMLSSearchLogo'] ) ? (bool) $instance['showMLSSearchLogo'] : true;
		$showSearchButton = isset( $instance['showSearchButton'] ) ? (bool) $instance['showSearchButton'] : false;
		
		$instance = wp_parse_args((array) $instance, $instance_opts);
		
		include( $this->getTemplateHierarchy( 'cs_template_listing-quick-search-widget_', 'listing-quick-search-widget-admin' ) );
	}
}

/**
 * Feature Listing Widget
 * @author ClickSold
 */
class Feature_Listing_Widget extends CS_Widget{
	
	private $PLUGIN_NAME = 'ClickSold Feature Listing Widget';
	private $PLUGIN_SLUG = 'cs-feature-listing-widget';
	private $PLUGIN_CLASSNAME = 'widget-feature-listing';
	private $PLUGIN_FEAT_LIST_OPTS = array();
	private $BROKERAGE = false;
	private $IDX = false;
	
	function Feature_Listing_Widget(){
	
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		global $BROKERAGE;
		global $IDX;
		
		$this->pluginDomain = 'feature_listing_widget';
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Add a section for viewing your listings'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		global $pagenow;
		
		$this->BROKERAGE = (bool) get_option("cs_opt_brokerage", "");
		
		if(get_option("cs_opt_tier_name", "") == "Platinum") $this->IDX = true;
		else $this->IDX = false;
		
		if( defined( "WP_ADMIN" ) && WP_ADMIN && 'widgets.php' == $pagenow ) {
			$this->get_feature_listing_options();
			$this->get_widget_scripts(true);
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) && !wp_script_is($this->PLUGIN_SLUG . '-js') ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget( $args, $instance ){
		global $wpdb;
		global $wp_rewrite;
		global $CS_GENERATED_PAGE_PARAM_CONSTANTS;
		global $blog_id;
		
		// Check if we should even display anything in the first place
		if($this->show_widget($instance) == false) return;
		
		$table_name = $wpdb->prefix . "cs_posts";
		
		//Get the ids of the idx and listings pages
		$page = $wpdb->get_row("SELECT postid, parameter FROM " . $table_name . " WHERE parameter = '" . $CS_GENERATED_PAGE_PARAM_CONSTANTS["listings"] . "'");
		
		$listings_id = $page->postid;
		
		if(is_null($listings_id)) return;
		
		// Get the pathname or query string of those pages
		if( $wp_rewrite->using_permalinks() ) $listings_url = $wpdb->get_var("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = " . $listings_id . " AND post_type = 'page' AND post_status != 'trash'");
		else $listings_url = $wpdb->get_var("SELECT guid FROM " . $wpdb->posts . " WHERE ID = " . $listings_id . " AND post_type = 'page' AND post_status != 'trash'");
		
		if(is_null($listings_url)) return;
		
		// Partial url for exclusive listings
		$listings_excl_url = $listings_url;
		
		if($wp_rewrite->using_permalinks()) {
			$listings_url .= '/';
			$listings_excl_url .= '/exclusive-';
		} else { 
			$listings_url .= '&mlsNum='; 
			$listings_excl_url .= '&listNum=';
		}
		
		// Turn urls absolute
		if(method_exists($this, 'is_multisite') && is_multisite()) {
			$listings_url = network_home_url($listings_url);
			$listings_excl_url = network_home_url($listings_excl_url);
		} else {
			$listings_url = home_url($listings_url);
			$listings_excl_url = home_url($listings_excl_url);
		}
		
		if( !array_key_exists('listing_type', $instance) || empty($instance['listing_type']) ) $instance['listing_type'] = 0;
		if( !array_key_exists('listing_status', $instance) || empty($instance['listing_status']) ) $instance['listing_status'] = 0;
		if( !array_key_exists('numDisp', $instance) || empty($instance['numDisp']) ) $instance['numDisp'] = '1';
		if( !array_key_exists('minCntWidth', $instance)) $instance['minCntWidth'] = '';
		if( !array_key_exists('maxCntWidth', $instance)) $instance['maxCntWidth'] = '';
		if( !array_key_exists('minCntHeight', $instance)) $instance['minCntHeight'] = '';
		if( !array_key_exists('maxCntHeight', $instance)) $instance['maxCntHeight'] = '';
		
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_feature-listing-widget_', 'feature-listing-widget' ) );
	}
	
	function update( $new_instance, $old_instance ){
		global $PLUGIN_FEAT_LIST_OPTS;
		global $BROKERAGE;
		global $IDX;
		
		if(empty($old_instance)) {
			if(empty($PLUGIN_FEAT_LIST_OPTS)) $this->get_feature_listing_options();
			$instance['listing_section'] = $PLUGIN_FEAT_LIST_OPTS['listing_section']['values'][0]['opt_val'];
			$instance['listing_type'] = $PLUGIN_FEAT_LIST_OPTS['listing_type']['values'][0]['opt_val'];
			$instance['listing_status'] = $PLUGIN_FEAT_LIST_OPTS['listing_status']['values'][0]['opt_val'];
		} else {
			$instance['title'] = $new_instance['title'];
			$instance['listing_type'] = $new_instance['listing_type'];
			$instance['listing_status'] = $new_instance['listing_status'];
		
			if($new_instance['listing_section'] == 3 && empty($new_instance['user_defined_listings'])) {
				$instance['listing_section'] == $old_instance['listing_section'];
				if(!empty($old_instance['user_defined_listings'])) $instance['user_defined_listings'] = $old_instance['user_defined_listings'];
			} else {
				$instance['listing_section'] = $new_instance['listing_section'];
				$instance['user_defined_listings'] = $new_instance['user_defined_listings'];
			}
		}
		
		if(empty($new_instance['freq']) || (int) $new_instance['freq'] < 1000) {
			$instance['freq'] = "1000";
		} else {
			$instance['freq'] = $new_instance['freq'];
		}
			
		if(empty($new_instance['numDisp']) || !is_numeric($new_instance['numDisp']) || ((int) $new_instance['numDisp'] < 1 && (int) $new_instance['numDisp'] > 10) ) {
			$instance['numDisp'] = "1";
		} else {
			$instance['numDisp'] = $new_instance['numDisp'];
		}
		
		$instance['minCntWidth'] = $new_instance['minCntWidth'];
		$instance['maxCntWidth'] = $new_instance['maxCntWidth'];
		$instance['minCntHeight'] = $new_instance['minCntHeight'];
		$instance['maxCntHeight'] = $new_instance['maxCntHeight'];
		
		return $instance;
	}
	
	function form( $instance ){	
		global $PLUGIN_FEAT_LIST_OPTS;
		global $BROKERAGE;
		global $IDX;
		
		if(empty($PLUGIN_FEAT_LIST_OPTS)) $this->get_feature_listing_options();
		
		$listing_section_label = $PLUGIN_FEAT_LIST_OPTS['listing_section']['label'];
		$listing_type_label = $PLUGIN_FEAT_LIST_OPTS['listing_type']['label'];
		$listing_status_label = $PLUGIN_FEAT_LIST_OPTS['listing_status']['label'];
		
		$instance_opts = array(
			'listing_section' => $PLUGIN_FEAT_LIST_OPTS['listing_section']['values'][0]['opt_val'],
			'listing_type' => $PLUGIN_FEAT_LIST_OPTS['listing_type']['values'][0]['opt_val'],
			'listing_status' => $PLUGIN_FEAT_LIST_OPTS['listing_status']['values'][1]['opt_val'],
			'freq' => '10000',
			'title' => '',
			'numDisp' => '1',
			'minCntWidth' => '',
			'maxCntWidth' => '',
			'minCntHeight' => '',
			'maxCntHeight' => '',
			'useDefault' => true,
			'user_defined_listings' => array()
		);
		
		$instance = wp_parse_args((array) $instance, $instance_opts);
		include( $this->getTemplateHierarchy( 'cs_template_feature-listing-widget_', 'feature-listing-widget-admin' ) );
	}
	
   /**
	*  Gets the "Show listings from section" and listing type options for the feature listing widget
	*/
	private function get_feature_listing_options(){
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		global $PLUGIN_FEAT_LIST_OPTS;
		
		$cs_request = new CS_request("pathway=604", $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return; 
		
		$json_response = $cs_response->cs_get_json();
		$PLUGIN_FEAT_LIST_OPTS = $json_response['featListWidgetOpts'];
	}
	
	/**
	 *  Tells us whether or not to show the widget based on if any results will be returned
	 */
	private function show_widget($instance){
		global $CS_SECTION_PARAM_CONSTANTS;
		
		$params = '&listSection=' . $instance['listing_section'] . '&listType=' . $instance['listing_type'] . '&listStatus=' . $instance['listing_status'];
		if(array_key_exists('numDisp', $instance) && !empty($instance['numDisp'])) {
			$params .= '&numDisp=' . $instance['numDisp'];
		} else {
			$params .= '&numDisp=1';
		}
				
		if(array_key_exists('user_defined_listings', $instance) && !empty($instance['user_defined_listings'])) {
			foreach($instance['user_defined_listings'] as $mlsNum) {
				$params .= '&userDefinedListings[]=' . $mlsNum;
			}
		}
		
		$cs_request = new CS_request('pathway=9' . $params, $CS_SECTION_PARAM_CONSTANTS["listings_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return false;
		
		$json_response = $cs_response->cs_get_json();
		if($json_response['total'] > 0) return true;
		else return false;
	}
}

/**
 * Feature Listing Widget
 * @author ClickSold
 */
class VIP_Widget extends CS_Widget{
	
	private $PLUGIN_NAME = 'ClickSold VIP Widget';
	private $PLUGIN_SLUG = 'cs-vip-widget';
	private $PLUGIN_CLASSNAME = 'widget-vip';
	private $BROKERAGE = false;
	
	function VIP_Widget(){
		
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		global $BROKERAGE;
		
		$this->pluginDomain = 'vip_widget';
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Adds the ClickSold VIP feature to any of your pages.'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		global $pagenow;
		
		$this->BROKERAGE = (bool) get_option("cs_opt_brokerage", "");
		
		if( defined( "WP_ADMIN" ) && WP_ADMIN && 'widgets.php' == $pagenow ) {
			$this->get_widget_scripts(true);
		}else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) && !wp_script_is($this->PLUGIN_SLUG . '-js') ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget( $args, $instance ){
		
		global $CS_SECTION_VIP_PARAM_CONSTANT;
	
		$cs_request = new CS_request("pathway=168&vipLoginCheck=true", $CS_SECTION_VIP_PARAM_CONSTANT["wp_vip_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return;
		
		$json_response = $cs_response->cs_get_json();
		
		$hideVIPOpts = "";
		if( !empty($instance['hideOpts']) ) $hideVIPOpts = "display:none;";
		
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_vip-widget_', 'vip-widget' ) );
	}
	
	function update( $new_instance, $old_instance ){
		$instance = $new_instance;
		if(empty($instance['hideOpts'])) $instance['hideOpts'] = 0;
		else $instance['hideOpts'] = 1;
		return $instance;
	}
	
	function form( $instance ){
		$instance_opts = array(
			'title' => 'VIP Options',
			'hideOpts' => 1
		);
		$instance = wp_parse_args((array) $instance, $instance_opts);
		include( $this->getTemplateHierarchy( 'cs_template_vip-widget_', 'vip-widget-admin' ) );
	}
	
}

/**
 * IDX Quick Search Widget
 * @author ClickSold
 */
class IDX_QS_Widget extends CS_WIDGET {
	
	private $PLUGIN_NAME = 'ClickSold IDX Quick Search Widget';
	private $PLUGIN_SLUG = 'cs-idx-qs-widget';
	private $PLUGIN_CLASSNAME = 'widget-idx-qs';
	private $PLUGIN_PROP_TYPES = array();
	
	function IDX_QS_Widget() {
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		
		$this->pluginDomain = 'idx_qs_widget';
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Adds the ability to run a IDX search from the current page'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		global $pagenow;
		if( defined( "WP_ADMIN" ) && WP_ADMIN && 'widgets.php' == $pagenow ) {
			$this->get_widget_scripts(true);
		}else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) && !wp_script_is($this->PLUGIN_SLUG . '-js') ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget( $args, $instance ) {
		global $wpdb;
		global $wp_rewrite;
		global $CS_GENERATED_PAGE_PARAM_CONSTANTS;
				
		$table_name = $wpdb->prefix . "cs_posts";
		
		//Get the relative url to the IDX search page
		$page = $wpdb->get_row("SELECT postid, parameter FROM " . $table_name . " WHERE parameter = '" . $CS_GENERATED_PAGE_PARAM_CONSTANTS["idx"] . "'");
		
		$idx_id = $page->postid;
		if(is_null($idx_id)) return;
		
		$using_permalinks = $wp_rewrite->using_permalinks();
		
		// Get the pathname or query string of those pages
		if( $using_permalinks ) {
			$idx_url = $wpdb->get_var("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = " . $idx_id . " AND post_type = 'page' AND post_status != 'trash'");
		} else {
			$using_permalinks = 0;
			
			$idx_url = $wpdb->get_var("SELECT guid FROM " . $wpdb->posts . " WHERE ID = " . $idx_id . " AND post_type = 'page' AND post_status != 'trash'");
			
			//Strip the root url
			$patt = "/\/\?/";
			$idx_url_parts = preg_split($patt, $idx_url);
			
			//Check if the guid is valid
			if(count($idx_url_parts) < 2) return;
			
			$idx_url = "?" . $idx_url_parts[1];
		}
				
		if(is_null($idx_url)) return;
		
		if($wp_rewrite->using_permalinks()) {
			$idx_url .= '/?';
		} else {
			$idx_url .= '&';
		}
		
		extract( $args );
		extract( $instance );
		
		$widget_styles = '';
		if(is_numeric($min_width)) $widget_styles .= 'min-width:' . $min_width . 'px;';
		if(is_numeric($min_height)) $widget_styles .= 'min-height:' . $min_height . 'px;';
		if(is_numeric($max_width)) $widget_styles .= 'max-width:' . $max_width . 'px;';
		if(is_numeric($max_height)) $widget_styles .= 'max-height:' . $max_height . 'px;';
		
		include( $this->getTemplateHierarchy( 'cs_template_idx-qs-widget_', 'idx-quick-search-widget' ) );
	}
	
	function update( $new_instance, $old_instance ) {
		if(!is_numeric($new_instance['min_width'])) $new_instance['min_width'] = '';
		if(!is_numeric($new_instance['min_height'])) $new_instance['min_height'] = '';
		if(!is_numeric($new_instance['max_width'])) $new_instance['max_width'] = '';
		if(!is_numeric($new_instance['max_height'])) $new_instance['max_height'] = '';
		$new_instance['compact_vers'] = !empty($new_instance['compact_vers']) ? 1 : 0;

		return $new_instance;
	}
	
	function form( $instance ) {
	    global $PLUGIN_PROP_TYPES;
		if(empty($PLUGIN_PROP_TYPES)) $this->get_property_types();
		$prop_types = $PLUGIN_PROP_TYPES;
		
		$instance_opts = array(
			'default_prop_type' => $PLUGIN_PROP_TYPES[0]['val'],
			'min_width' => '',
			'min_height' => '',
			'max_width' => '',
			'max_height' => '',
			'compact_vers' => ''
		);
		
		$compact_vers = isset( $instance['compact_vers'] ) ? (bool) $instance['compact_vers'] : false;

		$instance = wp_parse_args((array) $instance, $instance_opts);
		include( $this->getTemplateHierarchy( 'cs_template_idx-qs-widget_', 'idx-quick-search-widget-admin' ) );
	}
	
	/**
	*
	*/
	private function get_property_types() {
		global $PLUGIN_PROP_TYPES;
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		
		$cs_request = new CS_request('pathway=640&idxQuickSearch_loadConfig=true', $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return '';
		
		$response = $cs_response->cs_get_json();
		$PLUGIN_PROP_TYPES = $response['cs_idx_qs_prop_types'];
	}
}

/**
 * Community Search Widget
 * @author ClickSold
 */
class Community_Search_Widget extends CS_Widget{
	
	private $PLUGIN_NAME = 'ClickSold Community Search Widget';
	private $PLUGIN_SLUG = 'cs-community-search-widget';
	private $PLUGIN_CLASSNAME = 'widget-community-search';
	
	function Community_Search_Widget(){
		global $PLUGIN_NAME;
		global $PLUGIN_SLUG;
		global $PLUGIN_CLASSNAME;
		
		$this->pluginDomain = 'community_search_widget';
		
		$this->loadPluginTextDomain();
		$widget_opts = array(
			'classname' => $this->PLUGIN_CLASSNAME,
			'description' => 'Widget that allows you to run a community search'
		);
		
		$this->WP_Widget( $this->PLUGIN_SLUG, $this->PLUGIN_NAME, $widget_opts );
		
		global $pagenow;
		if( defined( "WP_ADMIN" ) && WP_ADMIN && 'widgets.php' == $pagenow ) {
			$this->get_widget_scripts(true);
		} else if( is_admin() === false && is_active_widget(false, false, $this->id_base, true) && !wp_script_is($this->PLUGIN_SLUG . '-js') ) {
			$this->get_widget_scripts(false);
		}
	}
	
	function widget( $args, $instance ){
		global $wpdb;
		global $wp_rewrite;
		global $CS_GENERATED_PAGE_PARAM_CONSTANTS;
		global $blog_id;
				
		$table_name = $wpdb->prefix . "cs_posts";
		
		//Get the id of the community page
		$page = $wpdb->get_row("SELECT postid, parameter FROM " . $table_name . " WHERE parameter = '" . $CS_GENERATED_PAGE_PARAM_CONSTANTS["community"] . "'");
		
		$community_id = $page->postid;
		
		if(is_null($community_id)) return;
		
		// Get the pathname or query string of those pages
		if( $wp_rewrite->using_permalinks() ) $community_url = $wpdb->get_var("SELECT post_name FROM " . $wpdb->posts . " WHERE ID = " . $community_id . " AND post_type = 'page' AND post_status != 'trash'");
		else $community_url = $wpdb->get_var("SELECT guid FROM " . $wpdb->posts . " WHERE ID = " . $community_id . " AND post_type = 'page' AND post_status != 'trash'");
		
		if(is_null($community_url)) return;
				
		if($wp_rewrite->using_permalinks()) {
			$community_url .= '/<city>/<neigh>/1';
			// Turn url absolute
			if(method_exists($this, 'is_multisite') && is_multisite()) $community_url = network_home_url($community_url);
			else $community_url = home_url($community_url);
		} else $community_url .= '&city=<city>&neigh=<neigh>'; 
				
		// Get the cities & neighbourhoods
		$regions_all = $this->get_regions($instance, $args['widget_id']);
		$regions = array();
		$city_list = array();
		
		foreach($regions_all as $city => $neigh) {
			if(!array_key_exists($city, $regions)) {
				$regions[$city] = $neigh;
				$city_list[] = $city;
			} else {
				$regions[$city] = array_push($regions[$city], $neigh);
			}
		}
		
		extract( $args );
		extract( $instance );
		include( $this->getTemplateHierarchy( 'cs_template_community-search-widget_', 'community-search-widget' ) );
	}
	
	function update( $new_instance, $old_instance ){
		if(empty($old_instance)) {
			$instance['title'] = 'Communities';
			$instance['incOrExcSelected'] = 1;
		} else {
			$instance['title'] = $new_instance['title'];
			$instance['incOrExcSelected'] = $new_instance['incOrExcSelected'];
			$instance['cities'] = $new_instance['cities'];
		}
				
		return $instance;
	}
	
	function form( $instance ){	
		$instance_opts = array(
			'title' => 'Communities',
			'incOrExcSelected' => 1,
			'cities' => array()
		);
		
		$instance = wp_parse_args((array) $instance, $instance_opts);
		$city_list_avail = $this->get_init_config_regions($instance);
		include( $this->getTemplateHierarchy( 'cs_template_community-search-widget_', 'community-search-widget-admin' ) );
	}
	
	private function get_init_config_regions($instance){
		global $CS_SECTION_ADMIN_PARAM_CONSTANT;
		
		$params = '&incOrExcSelected=' . $instance['incOrExcSelected'] . '&load_community_search_opts=true';
		if(array_key_exists('cities', $instance) && !empty($instance['cities'])) {
			foreach($instance['cities'] as $city) {
				$params .= '&cities[]=' . $city;
			}
		}
		
		$cs_request = new CS_request('pathway=640' . $params, $CS_SECTION_ADMIN_PARAM_CONSTANT["wp_admin_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return;
		
		return $cs_response->get_body_contents();
	}
	
	private function get_regions($instance, $widget_id){
		global $CS_SECTION_PARAM_CONSTANTS;
		
		$widget_id_parts = explode("-", $widget_id);
		$params = '&incOrExcSelected=' . $instance['incOrExcSelected'] . '&widgetId=' . end($widget_id_parts);
		if(array_key_exists('cities', $instance) && !empty($instance['cities'])) {
			foreach($instance['cities'] as $city) {
				$params .= '&cities[]=' . $city;
			}
		}
		
		$cs_request = new CS_request('pathway=659' . $params, $CS_SECTION_PARAM_CONSTANTS["community_pname"]);
		$cs_response = new CS_response($cs_request->request());
		if($cs_response->is_error()) return;
		
		return $cs_response->cs_get_json();
	}
}
?>
