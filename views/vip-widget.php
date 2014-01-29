<?php echo $before_widget; ?>
<div id="<?php echo $this->get_field_id("") ?>container" class="<?php echo $this->widget_options['classname'] ?>-container widget cs-vip-widget">
<?php if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } ?>
  <ul>
<?php if($json_response['logged_in'] == 1) { ?> 
    <li class="cs-vip-widget-login" style="display:none;"><a class="csVOWSignIn" href="#">Sign In</a> 
<?php   if($json_response['fb_login'] == 1) { ?>
     | <a href="<?php echo $json_response['fb_login_link'] ?>"><img class="cs-vip-widget-fb-button" src="<?php echo $json_response['fb_login_btn'] ?>" /></a>
<?php   } ?>
<?php   if($json_response['g_login'] == 1) { ?>
     | <a href="<?php echo $json_response['g_login_link'] ?>"><img class="cs-vip-widget-fb-button" src="<?php echo $json_response['g_login_btn'] ?>" /></a>
<?php   } ?>
    </li>
	<li class="cs-vip-widget-register" style="display:none;"><a class="csVOWRegister" href="#">Register</a></li>
	<li class="cs-vip-widget-logout"><span><?php echo $json_response['name']; ?> | </span><a class="csVOWLogout" href="#">Sign Out</a></li>
	<li class="cs-vip-widget-vip-account"><a id="csVOWAccount" href="#">Account</a></li>
	<li class="cs-vip-widget-vip-searches"><a id="csVOWSearches" href="#">Saved Searches</a></li>
	<li class="cs-vip-widget-vip-favorites"><a id="csVOWFavorites" href="#">Favorite Listings</a></li>
	<li class="cs-vip-widget-vip-analyzer"><a id="csVOWAnalyzer" href="#">Market Analyzer</a></li>
<?php } else { ?>
    <li class="cs-vip-widget-login"><a class="csVOWSignIn" href="#">Sign In</a>
<?php   if($json_response['fb_login'] == 1) { ?>
     | <a href="<?php echo $json_response['fb_login_link'] ?>"><img class="cs-vip-widget-fb-button" src="<?php echo $json_response['fb_login_btn'] ?>" /></a>
<?php   } ?>
<?php   if($json_response['g_login'] == 1) { ?>
     | <a href="<?php echo $json_response['g_login_link'] ?>"><img class="cs-vip-widget-fb-button" src="<?php echo $json_response['g_login_btn'] ?>" /></a>
<?php   } ?>	
    </li>
	<li class="cs-vip-widget-register"><a class="csVOWRegister" href="#">Register</a></li>
	<li class="cs-vip-widget-logout" style="display:none"><a class="csVOWLogout" href="#">Sign Out</a></li>
	<li class="cs-vip-widget-vip-account" style="<?php echo $hideVIPOpts ?>"><a id="csVOWAccount" href="#">Account</a></li>
	<li class="cs-vip-widget-vip-searches" style="<?php echo $hideVIPOpts ?>"><a id="csVOWSearches" href="#">Saved Searches</a></li>
	<li class="cs-vip-widget-vip-favorites" style="<?php echo $hideVIPOpts ?>"><a id="csVOWFavorites" href="#">Favorite Listings</a></li>
	<li class="cs-vip-widget-vip-analyzer" style="<?php echo $hideVIPOpts ?>"><a id="csVOWAnalyzer" href="#">Market Analyzer</a></li>
<?php } ?>
    
  </ul>
</div>
<script>
(function($){
	$(document).ready(function(){
		$("#<?php echo $this->get_field_id("") ?>container").csVOWPanel({
			ajaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php', dirname(__FILE__) ) ?>",
			vipAjaxTarget : "<?php echo plugins_url( 'CS_ajax_request.php?wp_vip_pname=wp_vip&#038;section=wp_vip', dirname(__FILE__) ) ?>",
			plugin : true,
			csWidget : true,
<?php if( empty($hideOpts) ) { ?>
			csWidgetHideOpts : false
<?php } else { ?>
			csWidgetHideOpts : true
<?php } ?>
		});
	});
})(csJQ);
</script>
<?php echo $after_widget; ?>
