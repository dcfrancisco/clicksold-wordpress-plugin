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

// Note: These have to be included before any output is sent to the browser so that servers with buffering disabled don't break when we try to start our session.
require_once('../../../wp-load.php');
?>

<div id="cs_admin_activation_page">
  <div id="cs-wrapper">
    <div class="cs-form-section">
      <h1>How do I... (will open in a new tab)</h1>
      <a href="http://www.clicksold.com/wiki/index.php/Domains_Setup_-_How_to_Add_and_Point_Your_Domain_Name%28s%29" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-domain-name.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Use my Personal Domain Name</div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/Search_Engine_Optimization" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-google-ranking.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Rank Well in <b><font color="#1a53f7">G</font><font color="#ef2f37">o</font><font color="#e98401">o</font><font color="#1a53f7">g</font><font color="#01a516">l</font><font color="#ef2f37">e</font></b></div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/How_to_Change_the_Website_Banner" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-change-banner.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Change my Website Banner</div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/ClickSold_Profile_Widget" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-personal-photo.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Add my Photo</div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/Social_Profiles_Widget" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-social-media.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Add Social Media Icons</div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/Get_Listings_from_Your_MLS%C2%AE" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-IDX-search.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Add IDX to my Website</div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/How_to_Create_Email_Addresses" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-email.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Integrate my Email</div>
      </a>
      <a href="http://www.clicksold.com/wiki/index.php/How_to_Upgrade_Your_ClickSold_Package" target="_blank" class="cs-admin-icon">
        <div class="cs-admin-icon-image"><img src="<?php echo plugins_url( "images/welcome-upgrade.png", __FILE__ ); ?>"></div>
        <div class="cs-admin-icon-text">Upgrade my Account</div>
      </a>
    </div>
  </div>

</div>
