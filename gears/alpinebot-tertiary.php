<?php


class PhotoTileForSmugMugBot extends PhotoTileForSmugMugBasic{  

/**
 *  Create constants for storing info 
 *  
 *  @ Since 1.2.2
 */
   public $out = "";
   public $options;
   public $wid; // Widget id
   public $results;
   public $shadow;
   public $border;
   public $curves;
   public $highlight;
   public $rel;
   
/**
 *  Update global (non-widget) options
 *  
 *  @ Since 1.2.3.2
 * ########################## TODO: replace get_option calls with $this->options ################
 */
  function updateGlobalOptions(){
    $options = $this->get_all_options();
    $defaults = $this->option_defaults(); 
    foreach( $defaults as $name=>$info ){
      if( !$info['widget'] && isset($options[$name]) ){
        $this->options[$name] = $options[$name];
      }
    }
  }
  
/**
 *  Function for creating cache key
 *  
 *  @ Since 1.2.2
 */
  function key_maker( $array ){
    if( isset($array['name']) && is_array( $array['info'] ) ){
      $return = $array['name'];
      foreach( $array['info'] as $key=>$val ){
        $return = $return."-".($val?$val:$key);
      }
      $return = @ereg_replace('[[:cntrl:]]', '', $return ); // remove ASCII's control characters
      $bad = array_merge(
        array_map('chr', range(0,31)),
        array("<",">",":",'"',"/","\\","|","?","*"," ",",","\'",".")); 
      $return = str_replace($bad, "", $return); // Remove Windows filename prohibited characters
      return $return;
    }
  }
  
/**
 * Alpine PhotoTile for SmugMug: Photo Retrieval Function
 * The PHP for retrieving content from SmugMug.
 *
 * @since 1.0.0
 * @updated 1.2.3
 */
   
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  ///////////////////////////////////////////    Generate Image Content    ////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  function photo_retrieval(){
    $smugmug_options = $this->options;
    $defaults = $this->option_defaults();

    $key_input = array(
      'name' => 'smugmug',
      'info' => array(
        'vers' => $this->vers,
        'src' => $smugmug_options['smugmug_source'],
        'uid' => $smugmug_options['smugmug_user_id'],
        'gallery' => $smugmug_options['smugmug_user_gallery'],
        'gkey' => $smugmug_options['smugmug_user_gallery_key'],
        'keyword' => $smugmug_options['smugmug_keyword'],
        'num' => $smugmug_options['smugmug_photo_number'],
        'link' => $smugmug_options['smugmug_display_link'],
        'text' => $smugmug_options['smugmug_display_link_text'],
        'size' => $smugmug_options['smugmug_photo_size']
        )
      );
    $key = $this->key_maker( $key_input );

    $disablecache = $this->get_option( 'cache_disable' );
    if ( !$disablecache ) {
      if( $this->cacheExists($key) ) {
        $results = $this->getCache($key);
        $results = @unserialize($results);
        if( count($results) ){
          $results['hidden'] .= '<!-- Retrieved from cache -->';
          $this->results = $results;
          return;
        }
      }
    }
    
    $message = '';
    $hidden = '';
    $continue = false;
    $feed_found = false;
    $linkurl = array();
    $photocap = array();
    $photourl = array();
                
    $smugmug_uid = apply_filters( $this->hook, empty($smugmug_options['smugmug_user_id']) ? '' : $smugmug_options['smugmug_user_id'], $smugmug_options );
    $smugmug_uid = str_replace(array('/',' '),'',$smugmug_uid);
    $smugmug_uid = str_replace('http:','',$smugmug_uid );
    $smugmug_uid = str_replace('.smugmug.com','',$smugmug_uid);
    $request = "";
    switch ($smugmug_options['smugmug_source']) {
      case 'user_recent':
        $request = 'http://api.smugmug.com/hack/feed.mg?Type=nicknameRecent&Data=' . $smugmug_uid . '&ImageCount='.$smugmug_options['smugmug_photo_number'].'&Paging=0&Size='.$smugmug_options['smugmug_photo_size'].'&format=rss';
      break;
      case 'user_popular':
        $request = 'http://api.smugmug.com/hack/feed.mg?Type=nicknamePopular&Data=' . $smugmug_uid . '&ImageCount='.$smugmug_options['smugmug_photo_number'].'&Paging=0&Size='.$smugmug_options['smugmug_photo_size'].'&format=rss';
      break;
      case 'user_gallery':
        $smugmug_gid = apply_filters( $this->hook, empty($smugmug_options['smugmug_user_gallery']) ? '' : $smugmug_options['smugmug_user_gallery'], $smugmug_options );
        $smugmug_gkey = apply_filters( $this->hook, empty($smugmug_options['smugmug_user_gallery_key']) ? '' : $smugmug_options['smugmug_user_gallery_key'], $smugmug_options );
        $request = 'http://api.smugmug.com/hack/feed.mg?Type=gallery&Data='.$smugmug_gid.'_'.$smugmug_gkey.'&ImageCount='.$smugmug_options['smugmug_photo_number'].'&Paging=0&Size='.$smugmug_options['smugmug_photo_size'].'&format=rss';
      break;
      case 'user_keyword':
        $smugmug_keyword = apply_filters( $this->hook, empty($smugmug_options['smugmug_keyword']) ? '' : $smugmug_options['smugmug_keyword'], $smugmug_options );
        $request = 'http://api.smugmug.com/hack/feed.mg?Type=userkeyword&NickName='.$smugmug_uid.'&Data='.$smugmug_keyword.'&ImageCount='.$smugmug_options['smugmug_photo_number'].'&Paging=0&Size='.$smugmug_options['smugmug_photo_size'].'&format=rss';
      break;
      case 'global_keyword':
        $smugmug_keyword = apply_filters( $this->hook, empty($smugmug_options['smugmug_keyword']) ? '' : $smugmug_options['smugmug_keyword'], $smugmug_options );
        $request = 'http://api.smugmug.com/hack/feed.mg?Type=keyword&Data='.$smugmug_keyword.'&ImageCount='.$smugmug_options['smugmug_photo_number'].'&Paging=0&Size='.$smugmug_options['smugmug_photo_size'].'&format=rss';
      break;
    } 
  
    ///////////////////////////////////////////////////
    ///  Try using wp_remote_get and rss_200  ///
    ///////////////////////////////////////////////////
    if ( function_exists('simplexml_load_file') ) {
      
      // XML doesn't seem to care if "www" is present or not
      $_smugmug_request  = @urlencode( $request );	// just for compatibility
      $_smugmug_xml = @simplexml_load_file( $_smugmug_request); // @ is shut-up operator

      if( empty($_smugmug_xml) || !$_smugmug_xml->channel ){ 
        $hidden .= '<!-- Failed using simplexml_load_file() and XML @ '.$request.' -->';
        $continue = false;
      }else{
        $title = $_smugmug_xml->channel->title;
        $link = $_smugmug_xml->channel->link;
        $s = 0; // simple counter

        if( $_smugmug_xml->channel->item[0]) {
          foreach( $_smugmug_xml->channel->item as $p ) {
            if( $s<$smugmug_options['smugmug_photo_number'] ){   
              $photocap[$s] = (string) $p->title;          
              // list of link urls
              $linkurl[$s] = (string) $p->link;
              // list of photo urls
              $photourl[$s] = (string) $p->guid;

              $pattern = '/'.$smugmug_options['smugmug_photo_size'].'/';
              $original = @str_replace($pattern, '/L/', $photourl[$s] );
              $pattern = '-'.$smugmug_options['smugmug_photo_size'].'.';
              $originalurl[$s] = @str_replace( $pattern, "-L.", $original );

              $s++;
            }else{
              break;
            }
          }
        }
        if(!empty($linkurl) && !empty($photourl)){
          // If set, generate smugmug link
          if( $smugmug_options['smugmug_display_link'] && !empty($smugmug_options['smugmug_display_link_text']) ) {
            $user_link = '<div class="AlpinePhotoTiles-display-link" >';
            $user_link .='<a href="'.$link.'" target="_blank" >';
            $user_link .= $smugmug_options['smugmug_display_link_text'];
            $user_link .= '</a></div>';
          }
          // If content successfully fetched, generate output...
          $continue = true;    
          $hidden .= '<!-- Success using simplexml_load_file() and XML -->';
        }else{
          $hidden .= '<!-- No photos found using simplexml_load_file() and XML @ '.$request.' -->';  
          $continue = false;
        }
      }
    }
    
    ////////////////////////////////////////////////////////
    ////      If still nothing found, try using RSS      ///
    ////////////////////////////////////////////////////////
    if( $continue == false ) {
      // RSS may actually be safest approach since it does not require PHP server extensions,
      // but I had to build my own method for parsing SimplePie Object so I will keep it as the last option.
      
      if(!function_exists(APTFSMUGUGbyTAP_specialarraysearch)){
        function APTFSMUGUGbyTAP_specialarraysearch($array, $find){
          foreach ($array as $key=>$value){
            if( is_string($key) && $key==$find){
              return $value;
            }
            elseif(is_array($value)){
              $results = APTFSMUGUGbyTAP_specialarraysearch($value, $find);
            }
            elseif(is_object($value)){
              $sub = $array->$key;
              $results = APTFSMUGUGbyTAP_specialarraysearch($sub, $find);
            }
            // If found, return
            if(!empty($results)){return $results;}
          }
          return $results;
        }
      }
      
      include_once(ABSPATH . WPINC . '/feed.php');
      
      if( !function_exists('return_noCache') ){
        function return_noCache( $seconds ){
          // change the default feed cache recreation period to 30 seconds
          return 30;
        }
      }

      add_filter( 'wp_feed_cache_transient_lifetime' , 'return_noCache' );
      $rss = @fetch_feed( $request );
      remove_filter( 'wp_feed_cache_transient_lifetime' , 'return_noCache' );

      if (!is_wp_error( $rss ) && $rss != NULL ){ // Check that the object is created correctly 
        // Bulldoze through the feed to find the items 
        $results = array();
        $title = @APTFSMUGUGbyTAP_specialarraysearch($rss,'title');
        $title = $title['0']['data'];
        $link = @APTFSMUGUGbyTAP_specialarraysearch($rss,'link');
        $link = $link['0']['data'];
        $rss_data = @APTFSMUGUGbyTAP_specialarraysearch($rss,'item');

        $s = 0; // simple counter
        if ($rss_data != NULL ){ // Check again
          foreach ( $rss_data as $item ) {
            //print_r( $item );
            if( $s<$smugmug_options['smugmug_photo_number'] ){
              $linkurl[$s] = $item['child']['']['link']['0']['data'];    
              $content = $item['child']['']['description']['0']['data'];     
              if($content){
                // For Reference: regex references and http://php.net/manual/en/function.preg-match.php
                // Using the RSS feed will require some manipulation to get the image url from smugmug;
                // preg_replace is bad at skipping lines so we'll start with preg_match
                // i sets letters in upper or lower case, s sets . to anything
                @preg_match("/<IMG.+?SRC=[\"']([^\"']+)/si",$content,$matches); // First, get image from feed.
                if($matches[ 0 ]){
                  // Next, strip away everything surrounding the source url.
                  // . means any expression and + means repeat previous
                  $photourl_current = @preg_replace(array('/(.+)src="/i','/"(.+)/') , '',$matches[ 0 ]);
                  // Finally, change the size. 
                    // [] specifies single character and \w is any word character
                  //$photourl[$s] = @preg_replace('/[_]500[.]/', "_".$smugmug_options['smugmug_photo_size'].".", $photourl_current );
                  $photourl[$s] = $photourl_current;
                  $pattern = '/'.$smugmug_options['smugmug_photo_size'].'/';
                  $original = @str_replace($pattern, '/L/', $photourl_current );
                  $pattern = '-'.$smugmug_options['smugmug_photo_size'];
                  $originalurl[$s] = @str_replace( $pattern, "-L", $original );

                  // Could set the caption as blank instead of default "Photo", but currently not doing so.
                  $photocap[$s] = $item['child']['']['title']['0']['data'];
                  $s++;
                }
              }
            }
            else{
              break;
            }
          }
        }
        if(!empty($linkurl) && !empty($photourl)){
          if( $smugmug_options['smugmug_display_link'] && !empty($smugmug_options['smugmug_display_link_text']) ) {
            $user_link = '<div class="AlpinePhotoTiles-display-link" >';
            $user_link .='<a href="'.$link.'" target="_blank" >';
            $user_link .= $smugmug_options['smugmug_display_link_text'];
            $user_link .= '</a></div>';
          }
          // If content successfully fetched, generate output...
          $continue = true;
          $hidden .= '<!-- Success using fetch_feed() and RSS -->';
        }else{
          $hidden .= '<!-- No photos found using fetch_feed() and RSS @ '.$request.' -->';  
          $continue = false;
          $feed_found = true;
        }
      }
      else{
        $hidden .= '<!-- Failed using fetch_feed() and RSS @ '.$request.' -->';
        $continue = false;
      }      
    }
      
    ///////////////////////////////////////////////////////////////////////
    //// If STILL!!! nothing found, report that SmugMug ID must be wrong ///
    ///////////////////////////////////////////////////////////////////////
    if( false == $continue ) {
      if($feed_found ){
        $message .= '- SmugMug feed was successfully retrieved, but no photos found.';
      }else{
        $message .= '- SmugMug feed not found. Please recheck your ID.';
      }
    }
      
    $results = array('continue'=>$continue,'message'=>$message,'hidden'=>$hidden,'user_link'=>$user_link,'image_captions'=>$photocap,'image_urls'=>$photourl,'image_perms'=>$linkurl,'image_originals'=>$originalurl);
    
    if( true == $continue && !$disablecache ){     
      $cache_results = $results;
      if(!is_serialized( $cache_results  )) { $cache_results  = maybe_serialize( $cache_results ); }
      $this->putCache($key, $cache_results);
      $cachetime = $this->get_option( 'cache_time' );
      if( $cachetime && is_numeric($cachetime) ){
        $this->setExpiryInterval( $cachetime*60*60 );
      }
    }
    $this->results = $results;
  }
  
  
  
/**
 *  Get Image Link
 *  
 *  @ Since 1.2.2
 */
  function get_link($i){
    $link = $this->options['smugmug_image_link_option'];
    $photocap = $this->results['image_captions'][$i];
    $photourl = $this->results['image_urls'][$i];
    $linkurl = $this->results['image_perms'][$i];
    $url = $this->options['custom_link_url'];
    $originalurl = $this->results['image_originals'][$i];
    
    if( 'original' == $link && !empty($photourl) ){
      $this->out .= '<a href="' . $photourl . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>';
      return true;
    }elseif( ('smugmug' == $link || '1' == $link)&& !empty($linkurl) ){
      $this->out .= '<a href="' . $linkurl . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>';
      return true;
    }elseif( 'link' == $link && !empty($url) ){
      $this->out .= '<a href="' . $url . '" class="AlpinePhotoTiles-link" target="_blank" title='."'". $photocap ."'".'>'; 
      return true;
    }elseif( 'fancybox' == $link && !empty($originalurl) ){
      $this->out .= '<a href="' . $originalurl . '" class="AlpinePhotoTiles-link AlpinePhotoTiles-lightbox" title='."'". $photocap ."'".'>'; 
      return true;
    }  
    return false;    
  }
  
/**
 *  Update photo number count
 *  
 *  @ Since 1.2.2
 */
  function updateCount(){
    if( $this->options['smugmug_photo_number'] != count( $this->results['image_urls'] ) ){
      $this->options['smugmug_photo_number'] = count( $this->results['image_urls'] );
    }
  }

/**
 *  Get Parent CSS
 *  
 *  @ Since 1.2.2
 */
  function get_parent_css(){
    $opts = $this->options;
    $return = 'width:100%;max-width:'.$opts['widget_max_width'].'%;padding:0px;';
    if( 'center' == $opts['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'margin:0px auto;text-align:center;';
    }
    elseif( 'right' == $opts['widget_alignment'] || 'left' == $opts['widget_alignment'] ){                          //  Optional: Set text alignment (left/right) or center
      $return .= 'float:' . $opts['widget_alignment'] . ';text-align:' . $opts['widget_alignment'] . ';';
    }
    else{
      $return .= 'margin:0px auto;text-align:center;';
    }
    return $return;
 }
 
/**
 *  Add Image Function
 *  
 *  @ Since 1.2.2
 *  @ Updated 1.2.3
 ** Possible change: place original image as 'alt' and load image as needed
 */
  function add_image($i,$css=""){
    if( !isset( $this->options['general_disable_right_click'] ) ){
      echo $this->options['general_disable_right_click'] = $this->get_option('general_disable_right_click');
    }
    $onContextMenu = ($this->options['general_disable_right_click']?'onContextMenu="return false;"':'');
    $this->out .= '<img id="'.$this->wid.'-tile-'.$i.'" class="AlpinePhotoTiles-image '.$this->shadow.' '.$this->border.' '.$this->curves.' '.$this->highlight.'" src="' . $this->results['image_urls'][$i] . '" ';
    $this->out .= 'title='."'". $this->results['image_captions'][$i] ."'".' alt='."'". $this->results['image_captions'][$i] ."' "; // Careful about caps with ""
    $this->out .= 'border="0" hspace="0" vspace="0" style="'.$css.'" '.$onContextMenu.' />'; // Override the max-width set by theme
  }
  
/**
 *  Credit Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_credit_link(){
    if( !$this->options['widget_disable_credit_link'] ){
      $by_link  =  '<div id="'.$this->wid.'-by-link" class="AlpinePhotoTiles-by-link"><a href="http://thealpinepress.com/" style="COLOR:#C0C0C0;text-decoration:none;" title="Widget by The Alpine Press">TAP</a></div>';   
      $this->out .=  $by_link;    
    }  
  }
  
/**
 *  User Link Function
 *  
 *  @ Since 1.2.2
 */
  function add_user_link(){
    $userlink = $this->results['user_link'];
    if($userlink){ 
      if($this->options['widget_alignment'] == 'center'){                          //  Optional: Set text alignment (left/right) or center
        $this->out .= '<div id="'.$this->wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $this->out .= 'style="width:100%;margin:0px auto;">'.$userlink.'</div>';
      }
      else{
        $this->out .= '<div id="'.$this->wid.'-display-link" class="AlpinePhotoTiles-display-link-container" ';
        $this->out .= 'style="float:'.$this->options['widget_alignment'].';max-width:'.$this->options['widget_max_width'].'%;"><center>'.$userlink.'</center></div>'; 
        $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>'; // Only breakline if floating
      }
    }
  }
  
/**
 *  Setup Lightbox Call
 *  
 *  @ Since 1.2.3
 */
  function add_lightbox_call(){
    if( "fancybox" == $this->options['smugmug_image_link_option'] ){
      $this->out .= '<script>jQuery(window).load(function() {'.$this->get_lightbox_call().'})</script>';
    }   
  }
  
/**
 *  Get Lightbox Call
 *  
 *  @ Since 1.2.3
 */
  function get_lightbox_call(){
    $this->set_lightbox_rel();
  
    $lightbox = $this->get_option('general_lightbox');
    $lightbox_style = $this->get_option('general_lightbox_params');
    $lightbox_style = str_replace( array("{","}"), "", $lightbox_style);
    $lightbox_style = str_replace( "'", "\'", $lightbox_style);
    
    $setRel = 'jQuery( "#'.$this->wid.'-AlpinePhotoTiles_container a.AlpinePhotoTiles-lightbox" ).attr( "rel", "'.$this->rel.'" );';
    
    if( 'fancybox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'titleShow: false, overlayOpacity: .8, overlayColor: "#000"');
      return $setRel.'if(jQuery().fancybox){jQuery( "a[rel^=\''.$this->rel.'\']" ).fancybox( { '.$lightbox_style.' } );}';  
    }elseif( 'prettyphoto' == $lightbox ){
      //theme: 'pp_default', /* light_rounded / dark_rounded / light_square / dark_square / facebook
      $lightbox_style = ($lightbox_style?$lightbox_style:'theme:"facebook",social_tools:false');
      return $setRel.'if(jQuery().prettyPhoto){jQuery( "a[rel^=\''.$this->rel.'\']" ).prettyPhoto({ '.$lightbox_style.' });}';  
    }elseif( 'colorbox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'height:"80%"');
      return $setRel.'if(jQuery().colorbox){jQuery( "a[rel^=\''.$this->rel.'\']" ).colorbox( {'.$lightbox_style.'} );}';  
    }elseif( 'alpine-fancybox' == $lightbox ){
      $lightbox_style = ($lightbox_style?$lightbox_style:'titleShow: false, overlayOpacity: .8, overlayColor: "#000"');
      return $setRel.'if(jQuery().fancyboxForAlpine){jQuery( "a[rel^=\''.$this->rel.'\']" ).fancyboxForAlpine( { '.$lightbox_style.' } );}';  
    }
    return "";
  }
  
/**
 *  Set Lightbox "rel"
 *  
 *  @ Since 1.2.3
 */
 function set_lightbox_rel(){
    $lightbox = $this->get_option('general_lightbox');
    $custom = $this->get_option('hidden_lightbox_custom_rel');
    if( $custom && !empty($this->options['custom_lightbox_rel']) ){
      $this->rel = $this->options['custom_lightbox_rel'];
      $this->rel = str_replace('{rtsq}',']',$this->rel); // Decode right and left square brackets
      $this->rel = str_replace('{ltsq}','[',$this->rel);
    }elseif( 'fancybox' == $lightbox ){
      $this->rel = 'alpine-fancybox-'.$this->wid;
    }elseif( 'prettyphoto' == $lightbox ){
      $this->rel = 'alpine-prettyphoto['.$this->wid.']';
    }elseif( 'colorbox' == $lightbox ){
      $this->rel = 'alpine-colorbox['.$this->wid.']';
    }else{
      $this->rel = 'alpine-fancybox-safemode-'.$this->wid;
    }
 }
  
/**
 *  Function for printing vertical style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_vertical(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
                      
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-vertical-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';

        for($i = 0;$i<$opts['smugmug_photo_number'];$i++){
          $has_link = $this->get_link($i);  // Add link
          $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
          $this->add_image($i,$css); // Add image
          if( $has_link ){ $this->out .= '</a>'; } // Close link
        }
        
        $this->add_credit_link();
      
      $this->out .= '</div>'; // Close vertical-parent

      $this->add_user_link();

    $this->out .= '</div>'; // Close container
    $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
    
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');

    $this->add_lightbox_call();
    
    if( $opts['style_shadow'] || $opts['style_border'] || $opts['style_highlight']  ){
      $this->out .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$this->wid.'-vertical-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }
  }  
/**
 *  Function for printing cascade style
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_cascade(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
    
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-cascade-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';
      
        for($col = 0; $col<$opts['style_column_number'];$col++){
          $this->out .= '<div class="AlpinePhotoTiles_cascade_column" style="width:'.(100/$opts['style_column_number']).'%;float:left;margin:0;">';
          $this->out .= '<div class="AlpinePhotoTiles_cascade_column_inner" style="display:block;margin:0 3px;overflow:hidden;">';
          for($i = $col;$i<$opts['smugmug_photo_number'];$i+=$opts['style_column_number']){
            $has_link = $this->get_link($i); // Add link
            $css = "margin:1px 0 5px 0;padding:0;max-width:100%;";
            $this->add_image($i,$css); // Add image
            if( $has_link ){ $this->out .= '</a>'; } // Close link
          }
          $this->out .= '</div></div>';
        }
        $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
          
        $this->add_credit_link();
      
      $this->out .= '</div>'; // Close cascade-parent

      $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
      
      $this->add_user_link();

    // Close container
    $this->out .= '</div>';
    $this->out .= '<div class="AlpinePhotoTiles_breakline"></div>';
   
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $this->add_lightbox_call();
    
    if( $opts['style_shadow'] || $opts['style_border'] || $opts['style_highlight']  ){
      $this->out .= '<script>
           jQuery(window).load(function() {
              if(jQuery().AlpineAdjustBordersPlugin ){
                jQuery("#'.$this->wid.'-cascade-parent").AlpineAdjustBordersPlugin({
                  highlight:"'.$highlight.'"
                });
              }  
            });
          </script>';  
    }
  }

/**
 *  Function for printing and initializing JS styles
 *  
 *  @ Since 0.0.1
 *  @ Updated 1.2.2
 */
  function display_hidden(){
    $this->out = ""; // Clear any output;
    $this->updateCount(); // Check number of images found
    $opts = $this->options;
    $this->shadow = ($opts['style_shadow']?'AlpinePhotoTiles-img-shadow':'AlpinePhotoTiles-img-noshadow');
    $this->border = ($opts['style_border']?'AlpinePhotoTiles-img-border':'AlpinePhotoTiles-img-noborder');
    $this->curves = ($opts['style_curve_corners']?'AlpinePhotoTiles-img-corners':'AlpinePhotoTiles-img-nocorners');
    $this->highlight = ($opts['style_highlight']?'AlpinePhotoTiles-img-highlight':'AlpinePhotoTiles-img-nohighlight');
    
    $this->out .= '<div id="'.$this->wid.'-AlpinePhotoTiles_container" class="AlpinePhotoTiles_container_class">';     
      // Align photos
      $css = $this->get_parent_css();
      $this->out .= '<div id="'.$this->wid.'-hidden-parent" class="AlpinePhotoTiles_parent_class" style="'.$css.'">';
      
        $this->out .= '<div id="'.$this->wid.'-image-list" class="AlpinePhotoTiles_image_list_class" style="display:none;visibility:hidden;">'; 
        
          for($i = 0;$i<$opts['smugmug_photo_number'];$i++){
            $has_link = $this->get_link($i); // Add link
            $css = "";
            $this->add_image($i,$css); // Add image
            
            // Load original image size
            if( "gallery" == $opts['style_option'] && !empty( $this->results['image_originals'][$i] ) ){
              $this->out .= '<img class="AlpinePhotoTiles-original-image" src="' . $this->results['image_originals'][$i]. '" />';
            }
            if( $has_link ){ $this->out .= '</a>'; } // Close link
          }
        $this->out .= '</div>';
        
        $this->add_credit_link();       
      
      $this->out .= '</div>'; // Close parent  

      $this->add_user_link();
      
    $this->out .= '</div>'; // Close container
    
    $disable = $this->get_option("general_loader");
    $highlight = $this->get_option("general_highlight_color");
    $highlight = ($highlight?$highlight:'#64a2d8');
    
    $this->out .= '<script>';
      if(!$disable){
        $this->out .= '
               jQuery(document).ready(function() {
                jQuery("#'.$this->wid.'-AlpinePhotoTiles_container").addClass("loading"); 
               });';
      }
    $this->out .= '
           jQuery(window).load(function() {
            jQuery("#'.$this->wid.'-AlpinePhotoTiles_container").removeClass("loading");
            if( jQuery().AlpinePhotoTilesPlugin ){
              jQuery("#'.$this->wid.'-hidden-parent").AlpinePhotoTilesPlugin({
                id:"'.$this->wid.'",
                style:"'.($opts['style_option']?$opts['style_option']:'windows').'",
                shape:"'.($opts['style_shape']?$opts['style_shape']:'square').'",
                perRow:"'.($opts['style_photo_per_row']?$opts['style_photo_per_row']:'3').'",
                imageLink:'.($opts['smugmug_image_link']?'1':'0').',
                imageBorder:'.($opts['style_border']?'1':'0').',
                imageShadow:'.($opts['style_shadow']?'1':'0').',
                imageCurve:'.($opts['style_curve_corners']?'1':'0').',
                imageHighlight:'.($opts['style_highlight']?'1':'0').',
                lightbox:'.($opts['smugmug_image_link_option'] == "fancybox"?'1':'0').',
                galleryHeight:'.($opts['style_gallery_height']?$opts['style_gallery_height']:'0').', // Keep for Compatibility
                galRatioWidth:'.($opts['style_gallery_ratio_width']?$opts['style_gallery_ratio_width']:'800').',
                galRatioHeight:'.($opts['style_gallery_ratio_height']?$opts['style_gallery_ratio_height']:'600').',
                highlight:"'.$highlight.'",
                pinIt:'.($opts['pinterest_pin_it_button']?'1':'0').',
                siteURL:"'.get_option( 'siteurl' ).'",
                callback: '.($opts['smugmug_image_link_option'] == "fancybox"?'function(){'.$this->get_lightbox_call().'}':'""').'
              });
            }
          });
        </script>';
        
  }
 
}

?>
