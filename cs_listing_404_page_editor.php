<?php 
/*
* Form used for modifying the contents of the "Listing not found" page when the server cannot find a listing.  
* Used in the ClickSold - My Listings page.
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

// Note: These have to be included before any output is sent to the browser so that servers with buffering disabled don't break when we try to start our session.
require_once('../../../wp-load.php');
require_once('cs_constants.php');

require_once('CS_request.php');
require_once('CS_response.php');

$cs_request = new CS_request("pathway=74&loadListing404Content=true", "wp_admin");
$cs_response = new CS_response($cs_request->request());
?>
<form id="listing_404_content_form" method="post" action="<?php echo plugins_url( "cs_page_settings.php", __FILE__ ); ?>" class="cs-form cs-form-inline">
  <div class="cs-form-section-inline-help">Note: Please leave blank to have the system display the default 404 / Listing unavailable page.</div>
  <div class="cs-form-section">
    <fieldset>
      <?php the_editor($cs_response->get_body_contents()); ?>
      <div class="cs-form-submit-buttons-box">
        <input id="cs-save-content" type="button" class="cs-button" value="Save" />
      </div>
	</fieldset>
  </div>
</form>
<script type="text/javascript">
  (function($){
	$(document).ready(function(){
		$("#listingsManager").ListingsManager("initCSListing404EditorView", <?php echo $_GET['id'] ?>);
	});
  })(csJQ);
</script>
