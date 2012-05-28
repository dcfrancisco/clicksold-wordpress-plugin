<?php
echo $before_widget;
if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
if ( !empty( $image ) ) {
	echo '<div class="'.$this->widget_options['classname'].'-image-container">';
	
	if ( $link ) {
		//echo '<a class="'.$this->widget_options['classname'].'-image-link" href="'.$link.'" target="'.$linktarget.'">';
		echo '<a class="'.$this->widget_options['classname'].'-image-link" href="'.$link.'">';
	}
	if ( $imageurl ) {
		echo "<img src=\"{$imageurl}\" style=\"";
		if ( !empty( $width ) && is_numeric( $width ) ) {
			echo "max-width: {$width}px;";
		}
		if ( !empty( $height ) && is_numeric( $height ) ) {
			echo "max-height: {$height}px;";
		}
		echo "\"";
		if ( !empty( $align ) && $align != 'none' ) {
			echo " class=\"align{$align}\"";
		}
		if ( !empty( $alt ) ) {
			echo " alt=\"{$alt}\"";
		} else {
			echo " alt=\"{$title}\"";					
		}
		echo " />";
	}

	if ( $link ) { echo '</a>'; }
	echo '</div>';
}
if ( !empty( $description ) ) {
	$text = apply_filters( 'widget_text', $description );
	echo '<div class="'.$this->widget_options['classname'].'-description" >';
	echo wpautop( $text );			
	echo "</div>";
}

if($showIcons == '0')
	$showIconClass = $this->widget_options['classname']."-contact-no-icon";
echo '<div class="'.$this->widget_options['classname'].'-contact-info" >';
if( !empty( $phone )){
	echo '<span class="'.$this->widget_options['classname'].'-contact-medium-label"><img class="'.$showIconClass.'" src="' . plugins_url('images/icon-telephone.png', dirname(__FILE__) ) . '"/>Phone:</span><span class="'.$this->widget_options['classname'].'-contact-medium-value">'.$phone.'</span><br/>';
}
if( !empty( $mobilePhone )){
	echo '<span class="'.$this->widget_options['classname'].'-contact-medium-label"><img class="'.$showIconClass.'" src="' . plugins_url('images/icon-mobile-phone.png', dirname(__FILE__) ) . '"/>Mobile:</span><span class="'.$this->widget_options['classname'].'-contact-medium-value">'.$mobilePhone.'</span><br/>';
}
if( !empty( $fax )){
	echo '<span class="'.$this->widget_options['classname'].'-contact-medium-label"><img class="'.$showIconClass.'" src="' . plugins_url('images/icon-fax.png', dirname(__FILE__) ) . '"/>Fax:</span><span class="'.$this->widget_options['classname'].'-contact-medium-value">'.$fax.'</span><br/>';
}
if( !empty( $email )){
	$emailAr = split("@", $email);
	echo '<span class="'.$this->widget_options['classname'].'-contact-medium-email"><a href="javascript:location.href = \'mailto:\' + \''.$emailAr[0].'\' + \'@\' + \''.$emailAr[1].'\';"><img class="'.$showIconClass.'" src="' . plugins_url('images/icon-email.png', dirname(__FILE__) ) . '"/>Email Me</a></span>';
}
echo "</div>";
echo $after_widget;
?>