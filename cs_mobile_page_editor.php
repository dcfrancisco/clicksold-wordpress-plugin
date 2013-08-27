<?php 
/*
* Form used for customizing the contents of the mobile site's front page.
* Used in the ClickSold - My Website page.
*
* Copyright (C) 2013 ClickSold.com
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

$cs_request = new CS_request("pathway=63&loadMobileSiteFrontPageContent=true", "wp_admin");
$cs_response = new CS_response($cs_request->request());
?>
<?php the_editor($cs_response->get_body_contents()); ?>
<script type="text/javascript">
  (function($){
	$(document).ready(function(){
		$("#websiteManagerSettings").WebsiteManagerSettings("initMobilePageEditor");
	});
  })(csJQ);
</script>
