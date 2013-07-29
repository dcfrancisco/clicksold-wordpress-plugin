=== ClickSold IDX ===
Contributors: ClickSold
Tags: idx, vow, rets, real estate, mls, realtor, listing, listings, craigslist, rental, google map, agent, broker, properties, trulia, zillow, dsidx
Requires at least: 3.0.0
Tested up to: 3.5.2
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

From FREE to $45/month. 

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


= 1.38 =
* Compatability with wp-property plugin and better detection of special page names when they are already being used.

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
