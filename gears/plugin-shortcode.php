<?php
/**
 * Alpine PhotoTile for SmugMug: Shortcode
 *
 * @ Since 1.1.1
 * @ Updated 1.2.3.2
 */
 
  function APTFSMUGbyTAP_shortcode_function( $atts ) {
    $bot = new PhotoTileForSmugMugBot();
    
    $optiondetails = $bot->option_defaults();
    $options = array();
    foreach( $optiondetails as $opt=>$details ){
      $options[$opt] = $details['default'];
      if( $atts[ $details['short'] ] ){
        $options[$opt] = $atts[ $details['short'] ];
      }
    }

    $id = rand(100, 1000);
    $bot->wid = 'id'.$id;
    $bot->options = $options;
    $bot->updateGlobalOptions();
    $bot->photo_retrieval();
    
    $bot->enqueue_style_and_script();
    
    $return .= '<div id="'.$bot->id.'-by-shortcode-'.$id.'" class="AlpinePhotoTiles_inpost_container">';
    $return .= $bot->results['hidden'];
    if( $bot->results['continue'] ){  
      if( "vertical" == $options['style_option'] ){
        $bot->display_vertical();
      }elseif( "cascade" == $options['style_option'] ){
        $bot->display_cascade();
      }else{
        $bot->display_hidden();
      }
      $return .= $bot->out;
    }
    // If user does not have necessary extensions 
    // or error occured before content complete, report such...
    else{
      $return .= 'Sorry:<br>'.$bot->results['message'];
    }
    $return .= $after_widget;
    $return .= '</div>';
    
    return $return;
  }
  add_shortcode( 'alpine-phototile-for-smugmug', 'APTFSMUGbyTAP_shortcode_function' );
   
?>