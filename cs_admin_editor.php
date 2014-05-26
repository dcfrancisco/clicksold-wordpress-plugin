<?php 
/*
* Used for displaying TinyMCE on ClickSold forms.
*
* Copyright (C) 2014 ClickSold.com
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

$response_text = '';
if(!is_null($_SERVER['QUERY_STRING'])) {
	$cs_request = new CS_request($_SERVER['QUERY_STRING'], "wp_admin");
	$cs_response = new CS_response($cs_request->request());
	$response_text = $cs_response->get_body_contents();
}

global $tinymce_version;
if(version_compare($tinymce_version, '4021-20140423', 'ge')) {
	// TinyMCE 4
	wp_editor($response_text, $_GET['editor_id'], array("wpautop" => false)); 	
	\_WP_Editors::enqueue_scripts();
	\_WP_Editors::editor_js();
} else { 
	// TinyMCE 3
	the_editor($response_text);
}
?>
<script type="text/javascript">
(function($){
	//Initialize File Upload
	tinyMCE.execCommand('mceAddControl', false, '<?php echo $_GET['editor_id'] ?>');
	$('.add_media').off('click').on('click', function() {
		formfield = $('#upload_image').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		return false;
	});
})(csJQ);
</script>