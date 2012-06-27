<?php
echo $before_widget;
echo '<div class="widget '.$this->widget_options['classname'].'-container cs-button-widget">';
echo '  <a href="'.$link.'" alt="'.$alt_text.'" class="cs-button-widget-link"><div class="cs-button-widget-link-overlay"></div></a>';
echo '  <div class="cs-button-widget-inner '.$this->widget_options['classname'].'-text-container" style="background: url(\''.$imageurl.'\') no-repeat scroll center bottom transparent;">';
echo '    '.$smallText.' <br/><span class="cs-button-widget-large-text">'.$largeText.'</span>';
echo '  </div>';
echo '</div>';
echo $after_widget;
?>