/**
 * Handles adding the cs drop down menu with the quick insert feature for the clicksold shortcodes.
 */
(function() {
	
	// The clicksold plugins url.
	var cs_plugin_url = '';
	
	/**
	 * CS Shortcodes, NOTE: These are defined in CS_shortcodes.php and these lists must match.
	 */
	var cs_shortcodes = [
		{ title: 'Listing Details (by MLS Num)', shortcode: '[cs_listing_details mlsNumber="&lt;mls_number&gt;"]' },
		{ title: 'Listing Details (by List Num)', shortcode: '[cs_listing_details listingNumber="&lt;listing_number&gt;"]' },
		{ title: 'Featured Listings Search', shortcode: '[cs_featured_listings]' },
		{ title: 'Community List (Platinum Only)', shortcode: '[cs_community_list]' },
		{ title: 'Community Search Results (Platinum Only)', shortcode: '[cs_community_results city="&lt;city_name&gt;" neigh="&lt;community_name&gt;"]' },
		{ title: 'MLS Map Search (Platinum Only)', shortcode: '[cs_idx_search]' },
	];


	// Setup the button.
    tinymce.PluginManager.add('cs_shortcodes', function( editor, url ) {

		// Create the menu that will be used by the menu button from the cs_shortcodes configured list.
		var csShortcodesMenu = []; var i;
		for( i = 0; i < cs_shortcodes.length; i++ ) {
			
			// First we create the onclick function.
			var onclickFunc = (function() {
								
				// Done so we can find the correct i which would normally just be set to the end of the array by the time the on click got called.
				var current_i = i;
								
				return function() {
					editor.insertContent(cs_shortcodes[current_i].shortcode);
				};
			})();
			
			// Now the menu item.
			var menuItem = {
								text: cs_shortcodes[i].title,
								onClick: onclickFunc
							};
						
			csShortcodesMenu.push(menuItem);
		}

		// Add the resulting button.
		editor.addButton('cs_shortcodes', {
			text: 'CS Shortcodes',
			icon: false,
			type: 'menubutton',
			menu: csShortcodesMenu
		});
	});

})();



