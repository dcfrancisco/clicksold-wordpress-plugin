<?php
/*
* This function generates the plugin activation / configuration page for ClickSold
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
		require_once('../../../wp-load.php');
		require_once("cs_constants.php");
		require_once("cs_functions.php");

?>

<div id="cs_admin_activation_page">
  <div id="cs_wrapper">
<?php
			
			// variables for the field and option names 
			global $cs_opt_plugin_key;
			global $cs_opt_plugin_num;
			global $cs_opt_plugin_hostname;
			
			//must check that the user has the required capability 
			if (!current_user_can('manage_options'))
			{
				wp_die( __('You do not have sufficient permissions to access this page.') );
			}
			
			//Check plugin host url to see if this is being hosted elsewhere
			$hosted = cs_is_hosted();
			
		    // Read in existing option value from database
			$opt_plugin_key_val = get_option( $cs_opt_plugin_key );
			$opt_plugin_num_val = get_option( $cs_opt_plugin_num );
			
			$readonly = "";
			if(empty($hosted)) $opt_plugin_hostname_val = get_option( $cs_opt_plugin_hostname, "" );
			else $readonly = 'readonly="readonly"';

			$updated = "false";
			
			// See if the user has posted us some information
			if( isset( $_POST[ $cs_opt_plugin_key ] ) && isset( $_POST[ $cs_opt_plugin_num ] ) ) {
				
				if(empty($hosted)){
					// Read their posted value
					$opt_plugin_key_val = $_POST[ $cs_opt_plugin_key ];
					$opt_plugin_num_val = $_POST[ $cs_opt_plugin_num ];

					// Save the posted value in the database
					update_option( $cs_opt_plugin_key, $opt_plugin_key_val );
					update_option( $cs_opt_plugin_num, $opt_plugin_num_val );
					
					$opt_plugin_hostname_val = $_POST[ $cs_opt_plugin_hostname ];
					update_option($cs_opt_plugin_hostname, $opt_plugin_hostname_val);
				}

				// Put an settings updated message on the screen
				$updated = "true";
			}
			
?>
    <form id="cs_activation_form" name="form1" method="post" action="">
      <div class="cs-form-section">
	    <fieldset>
	      <div class="cs-form-section-title">ClickSold Plugin Settings</div>
          <table class="form-table">
            <tr valign="top">
              <th scope="row"><label>Plugin Key</label></th>
              <td><input name="<?php echo $cs_opt_plugin_key; ?>"  <?php echo $readonly; ?>  id="<?php echo $cs_opt_plugin_key; ?>"type="text" value="<?php echo $opt_plugin_key_val; ?>" class="regular-text" /> (Missing your plugin key?  Click <a href="http://www.clicksold.com/sign-up/" target="_blank">here</a> to get one!)</td>
            </tr>
            <tr valign="top">
              <th scope="row"><label>Plugin Number</label></th>
              <td><input name="<?php echo  $cs_opt_plugin_num; ?>"  <?php echo $readonly; ?>  id="<?php echo  $cs_opt_plugin_num; ?>" type="text" value="<?php echo  $opt_plugin_num_val; ?>" class="regular-text" /> (Missing your plugin number?  Click <a href="http://www.clicksold.com/sign-up/" target="_blank">here</a> to get one!) </td>
            </tr>
<?php if(empty($hosted)){ ?>
            <tr valign="top">
              <th scope="row"><label>Plugin Host Name</label></th>
              <td>
                <input name="<?php echo  $cs_opt_plugin_hostname; ?>" id="<?php echo  $cs_opt_plugin_hostname; ?>" type="text" value="<?php echo  $opt_plugin_hostname_val; ?>" class="regular-text" /> (Please enter your site url, the plugin will only work on this domain)
                <div class="cs-opt-plugin-hostname-err"></div>
              </td>
            </tr>
<?php } ?>
            <tr>
              <th scope="row"><label>Plugin Status</label></th>
              <td><div id="plugin-settings-status-autorized">Authorized</div><div id="plugin-settings-status-not-autorized">Not Authorized</div></td>
            </tr>
          </table>
	    </fieldset>
	  </div>
<?php if(empty($hosted)){ ?>   
      <div class="cs-form-submit-buttons-box">
        <input type="submit" id="submit" name="Submit" class="cs-button" value="<?php esc_attr_e('Save Changes') ?>" />
      </div>
<?php } ?>	  
<?php if($updated == "true"){ ?>
      <div class="cs-form-feedback"><?php _e('Updated');?></div>
<?php } ?>
    </form>
  </div>
  <script type="text/javascript">	
	(function($){
		$(document).ready(function(){
			$("#csAccountManager").CSAccountManager('initCSSettingsForm', {
				csPluginSettingsFormTarget: '<?php echo plugins_url( "cs_admin_activation_page.php", __FILE__ ); ?>'
			});
		});
	})(jQuery);
  </script>
</div>
