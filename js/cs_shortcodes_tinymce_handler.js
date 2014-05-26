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
		
	tinymce.create('tinymce.plugins.cs_shortcodes', {
		
		init : function(ed, url) {
			
			// here url is the url of this actual js file the plugin's url is one above this.
			cs_plugin_url = url + '/../';

		},

		createControl : function(n, cm) {

			switch (n) {
				
				case 'cs_shortcodes':
					var csSB = cm.createSplitButton('cs_shortcodes_sb', {
						title: 'CS ShortCodes',
						image: cs_plugin_url + 'images/cs_shortcodes_tinymce_button.png',
						onclick: function() { // Re-implements the default behaviour to work around an undefined variable error in ControlManager.createSplitButton (which works but throws errors on the console).
							csSB.showMenu();
						},
					});
					
					csSB.onRenderMenu.add(function(c, m) {
						m.add({title : 'CS ShortCodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
						
						// Add all of the defined shortcodes.
						for(var i in cs_shortcodes) {
							
							var onclickFunc = (function() {
								
								// Done so we can find the correct i which would normally just be set to the end of the array by the time the on click got called.
								var current_i = i;
								
								return function() {
									tinyMCE.execCommand("mceInsertContent", false, cs_shortcodes[current_i].shortcode);
								};
							})();
							
							m.add({ title : cs_shortcodes[i].title, onclick : onclickFunc });
						}
					});
						
					// Return the new listbox instance
					return csSB;
			}
			return null;
		}
	});
	
	tinymce.PluginManager.add('cs_shortcodes', tinymce.plugins.cs_shortcodes);
})();
