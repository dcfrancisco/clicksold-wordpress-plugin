<?php
/*
* File used for holding required constants for use with ClickSold Listings plugin
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

	/**
	 * Variable constants for use with setting specific parameter views
	 * @var string
	 */
	$CS_SECTION_PARAM_CONSTANTS = array(
		"listings_pname" => "listings",
		"community_pname" => "communities",
		"idx_pname" => "idx",
		"assoc_pname" => "associates"
	);
	
	$CS_SECTION_ADMIN_PARAM_CONSTANT = array(
		"wp_admin_pname" => "wp_admin"
	);
	
	$CS_SECTION_VIP_PARAM_CONSTANT = array(
		"wp_vip_pname" => "wp_vip"
	);
	
	/**
	* Not really needed as it defaults to the normal controller but here for consistency
	*/
	$CS_SECTION_UTILS_PARAM_CONSTANTS = array(
		"listing_autoblog_pname" => "wp_listing_autoblog"
	);
	
	$CS_SECTION_CAPTCHA_IMG_PARAM_CONSTANT = "captcha";
	
	$CS_SECTION_MOBILE_PARAM_CONSTANT = "wp_mobile";
	
	/**
	 * Variable constants for the values stored as the 'parameter' field 
	 * in the wp_cs_posts table.  Subject to change...
	 */
	$CS_GENERATED_PAGE_PARAM_CONSTANTS = array(
		"listings" => "mlsnum",
		"community" => "community",
		"idx" => "junk",
		"associates" => "associates"
	);
	
	/**
	 * Variable constant used for configuring account type (Agent/Brokerage)
	 */
	$CS_VARIABLE_CONFIG_ACCOUNTTYPE = "_cs_account_config";
	
	/**
	 * Variable constants for parsing meta title pattern
	 */
	$CS_VARIABLE_LISTING_META_TITLE_VARS = array(
		"_cs_listing_address" => "%a",
		"_cs_listing_mls_num" => "%m",
		"_cs_listing_price" => "%p",
		"_cs_listing_prov_st" => "%z",
		"_cs_listing_town_city" => "%c",
		"_cs_listing_neigh" => "%n"
	);
	
	/**
	 * Variable constants for parsing meta title & description pattern
	 * for generated community search results page
	 */
	$CS_VARIABLE_COMMUNITY_META_TITLE_VARS = array(
		"_cs_community_town_city" => "%c",
		"_cs_community_neigh" => "%n"
	);
	
	/**
	 * Variable constants for parsing meta description pattern
	 * for generated listing details page
	 */
	$CS_VARIABLE_LISTING_META_DESC_VAR = array(
		"_cs_listing_address" => "%a",
		"_cs_listing_mls_num" => "%m",
		"_cs_listing_price" => "%p",
		"_cs_listing_prov_st" => "%z",
		"_cs_listing_town_city" => "%c",
		"_cs_listing_neigh" => "%n",
		"_cs_listing_desc" => "%d"
	);
	
	/**
	 * Variable constants for Open Graph functionality
	 */
	$CS_VARIABLE_LISTING_META_OG = array(
		"_cs_listing_og_title" => "og:title",
		"_cs_listing_og_desc" => "og:description",
		"_cs_listing_og_image" => "og:image",
		"_cs_listing_og_url" => "og:url",
		"_cs_listing_og_sitename" => "og:site_name"
	);
	
	/**
	 * Variable constants for parsing meta title for generated associate
	 * profile pages
	 */
	$CS_VARIABLE_ASSOCIATE_META_TITLE_VARS = array(
		"_cs_associate_first_name" => "%f",
		"_cs_associate_last_name" => "%l"
	);
	
	/**
	 * Variable constants for parsing meta description pattern
	 * for generated associate profile page
	 */
	$CS_VARIABLE_ASSOCIATE_META_DESC_VAR = array(
		"_cs_associate_first_name" => "%f",
		"_cs_associate_last_name" => "%l",
		"_cs_associate_desc" => "%d"
	);
		
	/**
	 * Variable constant for displaying plugin notifications in admin area
	 */
	$CS_VARIABLE_META_ADMIN_NOTIFICATIONS = "_cs_admin_notify";
	
	/**
	 * Array of constants used for displaying possible field options for the generated 
	 * listing details page header title
	 */
	$CS_VARIABLE_LISTING_META_TITLE_VARS_LEGEND = array(
		"Address" => $CS_VARIABLE_LISTING_META_TITLE_VARS["_cs_listing_address"],
		"MLS Number" => $CS_VARIABLE_LISTING_META_TITLE_VARS["_cs_listing_mls_num"],
		"List Price" => $CS_VARIABLE_LISTING_META_TITLE_VARS["_cs_listing_price"],
		"Province/State" => $CS_VARIABLE_LISTING_META_TITLE_VARS["_cs_listing_prov_st"],
		"City/Town" => $CS_VARIABLE_LISTING_META_TITLE_VARS["_cs_listing_town_city"],
		"Neigh" => $CS_VARIABLE_LISTING_META_TITLE_VARS["_cs_listing_neigh"]
	);
		
	/**
	 * Array constant for displaying listing detail url configuration options
	 */
	$CS_VARIABLE_LISTING_DETAILS_URL_VARS_LEGEND = array(
		"Address" => "%a",
		"List Price" => "%p",
		"Province/State" => "%z",
		"City/Town" => "%c",
		"Neigh" => "%n"
	);
	
	/**
	 * Array of constants used for displaying possible field options for the community 
	 * search results page header title
	 */
	$CS_VARIABLE_COMMUNITY_META_TITLE_VARS_LEGEND = array(
		"City/Town" => $CS_VARIABLE_COMMUNITY_META_TITLE_VARS["_cs_community_town_city"],
		"Neigh" => $CS_VARIABLE_COMMUNITY_META_TITLE_VARS["_cs_community_neigh"]
	);
	
	$CS_VARIABLE_ASSOCIATE_META_TITLE_VARS_LEGEND = array(
		"First Name" => $CS_VARIABLE_ASSOCIATE_META_TITLE_VARS["_cs_associate_first_name"],
		"Last Name" => $CS_VARIABLE_ASSOCIATE_META_TITLE_VARS["_cs_associate_last_name"],
	);
	
	/**
	 * Array keyed on prefix values that give us the proper headers for meta page settings 
	 */
	$CS_VARIABLE_PREFIX_META_HEADERS = array(
		"listings" => "Listing Details Pages",
		"communities" => "Community Search Results Pages",
		"idx" => "MLS<sup>&reg;</sup> Listings Search",
		"associates" => "Associate Profile Pages"
	);
	
	/**
	 * Array of vars for auto blog post title / content wildcards
	 */
	$CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS = array(
		"_cs_autoblog_mlsnumber" => "%m",
		"_cs_autoblog_date" => "%t",  //Can be either list date or sold date based on status
		"_cs_autoblog_price" => "%p",
		"_cs_autoblog_address" => "%a",
		"_cs_autoblog_city" => "%c",
		"_cs_autoblog_neigh" => "%n"
	);
	
	/**
	 * Array of vars for auto blog post content wildcards
	 */
	$CS_VARIABLE_AUTO_BLOG_CONTENT_VARS = array(
		"_cs_autoblog_desc" => "%d",
		"_cs_autoblog_main_image_link" => "%i",
		"_cs_autoblog_listing_details_link" => "%l"
	);
	
	$CS_VARIABLE_AUTO_BLOG_TITLE_LEGEND = array(
		"MLS&reg; Number" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_mlsnumber'],
		"List Date / Sold Date" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_date'],
		"List Price" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_price'],
		"Listing Address" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_address'],
		"Listing City" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_city'],
		"Listing Neigh." => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_neigh'],
	);
	
	$CS_VARIABLE_AUTO_BLOG_CONTENT_LEGEND = array(
		"MLS&reg; Number" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_mlsnumber'],
		"List Date / Sold Date" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_date'],
		"List Price" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_price'],
		"Listing Description" => $CS_VARIABLE_AUTO_BLOG_CONTENT_VARS['_cs_autoblog_desc'],
		"Main Image URL" => $CS_VARIABLE_AUTO_BLOG_CONTENT_VARS['_cs_autoblog_main_image_link'],
		"Listing Page URL" => $CS_VARIABLE_AUTO_BLOG_CONTENT_VARS['_cs_autoblog_listing_details_link'],
		"Listing Address" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_address'],
		"Listing City" => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_city'],
		"Listing Neigh." => $CS_VARIABLE_AUTO_BLOG_TITLE_CONTENT_VARS['_cs_autoblog_neigh'],
	);
	
	/**
	 * Regex pattern used in conjunction with preg_match_all for finding parts of $CS_VARIABLE_LISTING_DETAILS_URL_VAR in a string
	 */
	$CS_CUST_LIST_URL_SEARCH_PATTERN = '/%[azpcn]/';
	
	/**
	 * Default values for the Auto Blogger.
	 */
	global $cs_autoblog_default_post_title_active;
	$cs_autoblog_default_post_title_active = '%m : Just Listed';
	global $cs_autoblog_default_post_title_sold;
	$cs_autoblog_default_post_title_sold = '%m : Just Sold';

	global $cs_autoblog_default_post_content_active;
	$cs_autoblog_default_post_content_active = '<div style="text-align:center; padding:5px">
  <img src="%i" />
</div>
%d <a href="%l"> View Listing Details ... </a>';

	global $cs_autoblog_default_post_content_sold;
	$cs_autoblog_default_post_content_sold = '<div style="text-align:center; padding:5px">
  <img src="%i" />
</div>
%d <a href="%l"> View Listing Details ... </a>';

	$CS_VARIABLE_AUTO_BLOG_RPM_STATUS_VARS = array(
		"Active" => "1",
		"Sold" => "2"
	);
	
	/**
	 *  IP Addresses of the CS hosting servers.  Do not change unless absolutely necessary!!!
	 */
	$CS_VARIABLE_HOSTING_ID = "174.129.42.47";
	$CS_VARIABLE_HOSTING_ID2 = "184.72.247.186";
	
	/**
	 * Array consisting of admin menu items and associated configuration info
	 * NOTE: first two items go to the same page, WP creates a sub menu item with
	 * the same name as the top level menu item when sub menu items are created,
	 * so duplicating the menu slug on a sub menu item will allow us to name the
	 * first sub menu item differently
	 */
	$CS_ADMIN_MENU_ITEMS = array(
		'ClickSold' => array(
			'level' => 'top',
			'menu_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < Activation',
			'callback' => 'cs_generated_form',//'cs_plugin_admin_activation_page',
			'server' => true,//false
			'request' => 'account_manager'//new
		),
		'My Account' => array(  //Activation
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < Activation',
			'callback' => 'cs_generated_form',//'cs_plugin_admin_activation_page',
			'server' => true,//false
			'request' => 'account_manager'//new
		),
		'My Domains' => array(  //Domain Mgmt / SEO
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_domains',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Domains',
			'callback' => 'cs_generated_form',
			'server' => true,
			'request' => 'domain_manager'
		),
		'My Listings' => array(  //Listings
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_listings',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Listings',
			'callback' => 'cs_generated_form',
			'server' => true,
			'request' => 'listings_manager'
		),
		'My Clients' => array(
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_clients',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Clients',
			'callback' => 'cs_generated_form',
			'server' => true,
			'request' => 'client_manager'
		),
		'My Associates_agent' => array(
			'name' => 'My Team',
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_associates',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Team',
			'callback' => 'cs_generated_form',
			'server' => true,
			'brokerage' => 0,
			'request' => 'agent_manager'
		),
		'My Associates_brok' => array(
			'name' => 'My Office',
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_associates',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Office',
			'callback' => 'cs_generated_form',
			'server' => true,
			'brokerage' => 1,
			'request' => 'brok_agent_manager'
		),
		'My Brokerage' => array(
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_brokerage',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Brokerage',
			'callback' => 'cs_generated_form',
			'server' => true,
			'brokerage' => 1,
			'request' => 'agent_manager'
		),
		'My Website' => array(  //Settings
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_admin_settings',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < Settings',
			'callback' => 'cs_generated_form',
			'server' => true,
			'request' => 'plugin_manager'
		),
		'Upgrade!' => array(
			'level' => 'sub',
			'menu_slug' => 'cs_plugin_product_config_direct',
			'parent_slug' => 'cs_plugin_admin',
			'page_title' => 'ClickSold < My Account',
			'callback' => 'cs_generated_form',//'cs_plugin_admin_activation_page',
			'server' => true,//false
			'request' => 'account_manager'//new
		),
		'Help' => array(
			'level' => 'sub',
			'menu_slug' => '', // Unused for external links.
			'parent_slug' => 'cs_plugin_admin',
//			'page_title' => '', // Unused for external links.
//			'callback' => '', // Unused for external links.
//			'server' => false,  // Unused for external links.
			'external_link' => "http://www.clicksold.com/wiki/index.php/Main_Page' target='_blank" // Yes I know that this is a dirty hack to get it to open in a diff tab / window.
		)
	);
	
?>