=== ClickSold IDX ===
Contributors: ClickSold
Tags: idx, vow, rets, real estate, mls, realtor, listing, listings, craigslist, rental, google map, agent, broker, properties, trulia, zillow, dsidx
Requires at least: 3.0.0
Tested up to: 4.2.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds listings, IDX/VOW search, and other real estate tools to your WordPress website. Integrated with many MLS&reg; systems in the US and Canada.

== Description ==

ClickSold adds real estate related features to Agent and Brokerage websites built on WordPress. Features include:

* Adding / Displaying real estate listings
* Adding Team members / office agents
* Customizable real estate widgets
* Optional MLS &reg; Integration
* Mobile listing browsing and searching
* Search Engine Optimized design

**See ClickSold in Action**

* Responsive Design: [Eleven40 Theme](http://eleven40demo.clicksold.com/)
* Gorgeous IDX Search: [Associate Theme](http://associatedemo.clicksold.com/mls-search/)

**How Much?**

From FREE to $45/month. (Platinum package includes one month free trial).

**Work with REALTORS&reg;?**

Sign up to become a ClickSold Affiliate and earn revenue from every Silver, Gold or Platinum ClickSold plugin you use.

* ClickSold Affiliate Program: [Click Here](http://www.clicksold.com/affiliate-program/)
* Our Existing Affiliates: [Click Here](http://www.clicksold.com/our-affiliates/)

== Installation ==

1. Install the plugin using the WordPress built in plugin installer or by uploading the folder in the clicksold-<version>.zip file into your plugins directory and activate the ClickSold plugin.
1. Go to http://www.clicksold.com/sign-up/ to signup. You will be e-mailed your plugin number and plugin key.
1. Fill in the plugin number, the plugin key as well as the primary domain name of your WordPress installation.

== Frequently Asked Questions ==

= How do I get my plugin number and key? =

Just go to http://www.clicksold.com/sign-up/ and register. We'll email you a number and key immediately!

== Screenshots ==

1. MLS &reg; Search
2. MLS &reg; Quick Search widget
3. Agent business card widget
4. Featured Listings display
5. Listing details display

== Changelog ==

= 1.67 =
* Fix php notice in strict mode.

= 1.66 =
* Fix Captcha on mobile site (when browsed on mobile defices).
* Brokerage - Added Associate list and profile shortcodes.
* Upstream - Added Grande Prairie - GPREB support.

= 1.65 =
* Shortcode support for new Advanced Search feature from upstream.
* Upstream - Advanced Search feature.
* Upstream - Nearest town filter added for CREB

= 1.64 =
* Fix incompatability with theme customizer

= 1.63 =
* Compatability fix on trying to figure out if CS is supposed to process on a given page load.
* Added My Emails section on plugins hosted with ClickSold
* Feature Listings - Tags shortcode functionality
* Upstream - Listing tagging feature for Featured Listings section.
* Upstream - ACTRIS - quick search now searches subdivisions.

= 1.62 =
* Featured Listings widget updates.
* Upstream - Reorganize Community data for ACTRIS(Austin) region.
* Upstream - Mitigate compatability issues between CS and other plugins that use the Google Maps api.
* Upstream - Listing view statistics are now global as opposed to per account.
* Upstream - MLS Search Engine - polygon searching is now more efficient.

= 1.61 =
* Added header quick search widget.  Ideal use would be in the header or page title sections.
* Upstream - Updated Mortgage calculator to make the CMHC optional while at the same time providing warnings if the options selected don't make sense. 
* Upstream - Updated contact form anti spam measures.
* Upstream - New JQ versions on mobile site.
* Upstream - ACTRIS better handling of subdivision data.

= 1.60 =
* Fix file uploads in the back office.

= 1.59 =
* Performance optimization fixes / updates.

= 1.58 =
* Fix VIP clients unable to login via username / pass - due to performance updates.

= 1.57 =
* Performance related updates.
* Upstream - VIP functionality can now be disabled on mobile site.

= 1.56 =
* Updated form styles and minor fixes.
* Upstream - Featured Listings Widget - fixes.
* Upstream - VIP Prompt on Listing Details
* Upstream - Cleaned up Community section values in all regions.
* Upstream - VOW Listings can now be shown on the MLS Search engine to non logged in users, prompt to register if clicked.
* Upstream - Added FaceBook listings integration, my listings can now be shown on a facebook page.
* Upstream - My Listings now has an optional view that plots the listings on a map as well as presenting the list.
* Upstream - Captchas updated to make them harder to scan and added reload button.
* Upstream - Reorganized settings section.
* Upstream - MLS Search - Added ability to save sort default 
* Upstream - Open Houses can now be advertised, are automatically loaded in regions where the data is available.

= 1.55 =
* Shortcode insertion button now works again with WP 3.9 and up.
* MLS Quick Search widget - now has compact option.
* Fix support for subdirectory WP installs.
* TinyMCE in ClickSold -> Site settings support.
* ClickSold -> Site settings re-organized.
* Upstream - Fix Mobile Contact form submission.
* Upstream - Search filters can now be added on MLS search when you're logged into the admin panel - used to create search by code predefined searches.
* Upstream - Added VIP gating on listing details views.
* Upstream - Listings Manager now suggests the board provided virtual tour link making it easy to add it to a listing. 
* Upstream - WP Plugin now considers localhost and 127.0.0.* as authorized domains. 
* Upstream - Cleaned up Community and City names in Vancouver.
* Upstream - Client manager updates and fixes.
* Upstream - Add community search links to the sitemap generated by the Better WordPress Google XML Sitemap plugin

= 1.54 =
* Enhanced Better Wordpress Google XML Sitemaps plugin integration.
* Upstream - MLS Community Search - Sorting for "All" property type.
* Upstream - MLS Community Search - List - Quick filter by name.
* Upstream - Featured Listings - Ability to view ONLY Exclusive listings.
* Upstream - Client Manager - Grid - now supports pagination.

= 1.53 =
* Basic Better Wordpress Google XML Sitemaps plugin integration.
* Fix Social Login when site does not have any CS components on the main page (and therefore cs js is not included)
* Upstream - Communities section now allows sort for the 'All' property type.
* Upstream - Mobile site now uses contact form as opposed to exposing the e-mail address.
* Upstream - ClickSold is now available for the Central Alberta Realtors Association (CARA).
* Upstream - Updated jQuery Mobile to 1.4.2 

= 1.52 =
* Fix Social login.
* Fix ajax endpoint so it works with certain hosting platforms where the ob_get_length function does not work correctly.
* Upstream - System now tracks statistics on the views of individual listings. Available in My Listings -> A Listing -> View Stats (tab).

= 1.51 =
* Added feature to supress the output of CS included CSS and JS.
* Added '~', '|' and '*' as possible delimiters for the listing details links.
* Add support for Facebook and Google vip login.
* Upstream - FB and Google VIP login feature.
* Upstream - MLS Search now pops up warning when user tries to draw using double clicks.
* Upstream - Fix Community Search Results 'Browse All' link disappears on second change of property type.
* Upstream - Listing Details page titles now correctly get the neighbourhood variable.

= 1.50 =
* CS Widgets now correctly initalize from the moment that they are placed in a sidebar (as opposed to having to reload the widgets page or save the widget).
* Upstream - Fix Mobile VIP signup and features.
* Upstream - Added Exclusive Listings Only filter for listings manager.
* Upstream - MLS Search - Search By Code interface now does not show up when viewing the listing details in an in interface tab. 

= 1.49 =
* Fix XSS on listing not found page editor.
* CS Widgets now get restored should the widget sidebars be updated while the CS plugin is deactivated, before in this case all cs widgets would be removed.
* Fix php warnings when adding widgets if wp_debug is on.
* CS Debug Info now replaces post content with the length of that content as having that much html frequently messes up the display of the pages that have the debug data on them.
* Upstream - Fix cs mobile site not showing office phone number.
* Upstream - Fix QS Listing widget functionality when editing the suggestions already presented.
* Upstream - MLS Search - Show listings in tabs now has extra option to use browser tabs instead of interface ones.

= 1.48 =
* Fixed error notices when WP_DEBUG is enabled.
* Fixes to Community Search Widget admin panel.

= 1.47 =
* Added Community Search Widget.
* Fixed menu item management on plugin capabilities re-synch.
* Fixed issue with header includes being added more than once on page load
* Modified Feature Listings widget to not show if no results will be returned - and other bug fixes.
* Added functionality for user defined listings in Featured Listings widget

= 1.46 =
* CS now correctly cleans up menu item posts when being deactivated assuming that the allow CS to update menus option is on.
* CS now correctly re-links manually specified menu items corresponding to it's pages during a deactivation / reactivation cycle.

= 1.45 =
* Fix IDX Quick Search Widget.

= 1.44 =
* Auto-Blogger now more reliable and resiliant to timing issues.

= 1.43 =
* Compatibility with Next GEN Gallery plugin.
* Added function to allow for disabling CS on a specific page.

= 1.42 =
* Added IDX Quick Search Widget.

= 1.41 =
* Better save / restore of state across plugin deactivation / reactivation for special CS generated pages.
* Optimization to prevent duplicate call to CS plugin server to fetch widget scripts.

= 1.40 =
* Ability to customize the mobile site's front page

= 1.39 =
* Added VIP functionality to mobile site

= 1.38 =
* Compatability with wp-property plugin and better detection of special page names when they are already being used.
* Allow Genesis custom document title to work on cs generated pages.

= 1.37 =
* Help link framework.

= 1.36 =
* Use new 3.5+ media uploader for custom widget images.

= 1.35 =
* Correctly process CS special pages when they are children pages.

= 1.34 =
* Added feature to strip out unwanted automatically appended url parameters.

= 1.33 =
* Compatability work around for Thesis theme framework.
* Extended VIP functionality so widgets can run VIP methods without VIP panel or VIP widget present

= 1.32 =
* Communties search results now works even if communties list page is disabled.
* VIP login widget no longer crashes when incorrect plugin credentials are used.
* Upgrade to Platinum prompts to remove demo exclusive listing.

= 1.31 =
* Fix listing details can't be shown if listings/ page is set to not show.
* Fix menu item duplication issue.
* Fix page js being executed on plugin activation page.

= 1.30 =
* Open Graph / fixes.

= 1.29 =
* Added dynamic Open Graph support for Listing Details in MLS Search. 

= 1.28 =
* Autoblogger fixes.

= 1.27 =
* Hide the My Domains menu item more reliably for 3rd party hosted sites (even if the account type is not technically correct).
* Support for listing 404 pages with customizable content.
* Open graph support for FB and other social media.
* Added support for up and coming search by code feature.

= 1.26 =
* Added editable content for listings details 404 page.

= 1.25 =
* Added wp debug info reporting feature.

= 1.24 =
* Made authorization process clearer.

= 1.23 =
* Added ability to specify property addresses in the listing details urls.

= 1.22 =
* Added listing status options for Featured Listings Widget

= 1.21 =
* Fix canonical links.
* Fix featured listings widget not respecting cycle frequency.

= 1.20 =
* JQuery isolation ... Optional jQuery sharing to come in next version.

= 1.19 =
* Optionally track sessions with cs plugin server using a cookie if php sessions are not available on the host.

= 1.18 =
* Server connection issues workaround.
* CS Shortcode compatability option with themes that try to format the_content after shortcode output.

= 1.17 =
* Interplugin - Compatibility fix.

= 1.16 =
* Fix featured listings widget when used on subpages.

= 1.15 =
* Fix metatag wildcard replacement causing string index errors in certain edge cases.
* Replaced is_main_query call for compatability with WP earlier than 3.3

= 1.14 =
* Added remote re-synch ability.
* Added debug features.
* Remove incorrectly implemented compatability with Shortcodes Ultimate plugin.

= 1.13 =
* Shortcode compatability with Shortcodes Ultimate plugin.
* Auth update now re-synchs capabilities automatically.
* Canonical link references fix now works with pre 3.4 wp versions.
* Shortcode fixes for shorcodes with multiple parameters
* Page settings wildcards now work w/o trailing space.

= 1.12 =
* Fix plugin re-configuration on product tier change.
* Fix canonical link references for CS generated pages.

= 1.11 =
* Admin notifications to make sure that accounts are properly configured.

= 1.10 =
* Cleanly support servers where phps output buffering is disabled.

= 1.9 =
* CS - Quick Search Widget now utilizes communities page for applicable searches.

= 1.8 =
* Added custom brokerage logo upload functionality.

= 1.7 =
* Fix autoblogger compatability with installs that don't support DateTime->diff.
* Fix cs generated content being mangled by certain themes.

= 1.6 =
* Disabled autoblogger if DateTime->diff method does not exist.

= 1.5 =
* Support for subdirectory wp installs.

= 1.4 =
* Fix warnings for lower priviledged logged in users.
* Initial installation bugfixes.

= 1.3 =
* Sessions now started earlier so as to not conflict with certain themes.
* Fixed captcha support on hosts that do not have php-gd installed.
* Added option to suppress menu manipulation.
* Added VIP widget.

= 1.2 =
* Updates to make plugin work smoother when hosted outside the ClickSold hosting environment.
* CS Shortcodes can now accept duplicate parameters.
* MLS Quick Search widget bug fixes.
* Provided widgets now correctly use the sidebar container values.
* Mobile site now detects certain new blackberry devices.

= 1.1 =
* Initial public release. 

== Upgrade Notice ==

= 1.2 =
* N/A

= 1.1 =
* N/A - Initial Release

== Requirements ==
* (optional) - php GD library for more reliable CAPTCHA display.
* (theme) - The ClickSold plugin relies on jQuery. Your theme needs to include jQuery using the standard wordpress script procedures. [Details](http://www.clicksold.com/wiki/index.php/Custom_Theme_is_Broken)
* (theme) - The ClickSold plugin relies on wp_head() and wp_footer() to be called in the appropriate places. [Details](http://www.clicksold.com/wiki/index.php/Custom_Theme_is_Broken)
