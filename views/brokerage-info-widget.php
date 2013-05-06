<?php 
echo $before_widget;
echo '<div class="'.$this->widget_options['classname'].'-container cs-widget">';
if(!empty($instance['name']))
	echo '  <h4>'. $instance['name'] .'</h4>';
if(!empty($instance['addr']))
	echo '  '. $instance['addr'] .'<br />';
if(!empty($instance['phone']))
	echo '  <b>Phone:</b> '. $instance['phone'] .'<br />';
if(!empty($instance['fax']))
	echo '  <b>Fax:</b> '. $instance['fax'] .'<br />';
if(!empty($instance['email'])){
	$emailAr = split("@", $instance['email']);
	echo '  <a href="javascript:location.href = \'mailto:\' + \''.$emailAr[0].'\' + \'@\' + \''.$emailAr[1].'\';"><b>Email Office</b></a><br />';
}
if(!empty($instance['web']))
	echo '  <a href="'. $instance['web'] .'" target="_blank">';
if($instance['logo_src'] == "upload_custom" && !empty($instance['upload_logo_src']))
	echo '    <img src="'. $instance['upload_logo_src'] .'" />';
else
	echo '    <img src="'. $instance['logo_src'] .'" />';
if(!empty($instance['web']))
	echo '  </a>';
if(!empty($instance['text']))
	echo '  <div style="word-wrap:break-word;">'. $instance['text'] .'</div>';
echo '</div>';
echo $after_widget;
?>