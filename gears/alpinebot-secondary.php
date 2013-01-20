<?php


class PhotoTileForSmugMugBasic extends PhotoTileForSmugMugBase{  
/**
 *  Simple function to get option setting
 *  
 *  @ Since 1.2.0
 */
  function get_option( $option_string ){
    $options = get_option( $this->settings );
    // No need to initialize options since defaults are applied as needed
    return ( NULL!==$options[$option_string] ? $options[$option_string] : $this->set_default_option( $options, $option_string ) );
  }
/**
 *  Simple function to array of all option settings
 *  
 *  @ Since 1.2.0
 */
  function get_all_options(){
    $options = get_option( $this->settings );
    $defaults = $this->option_defaults(); 
    foreach( $defaults as $option_string => $details ){
      if( NULL === $options[$option_string] ){
        $options[$option_string] = $default_options[$option_string]['default'];
      }
    }
    //update_option( $this->settings, $options ); Unnecessary since options will soon be updated if this fuction was called
    return $options;
  }
/**
 *  Correctly set and save the option's default setting
 *  
 *  @ Since 1.2.0
 */
  function set_default_option( $options, $option_string ){
    $default_options = $this->option_defaults();
    if( NULL !== $default_options[$option_string] ){
      $options[$option_string] = $default_options[$option_string]['default'];
      update_option( $this->settings, $options );
      return $options[$option_string];
    }else{
      return NULL;
    }
  }
/**
 *  Create array of option names for a given tab
 *  
 *  @ Since 1.2.0
 */
  function get_options_by_tab( $tab = 'generator' ){
    $default_options = $this->option_defaults();
    $return = array();
    foreach($default_options as $key => $val){
      if( $val['tab'] == $tab ){
        $return[$key] = $key;
      }
    }
    return $return;
  }
/**
 *  Create array of option names and current values for a given tab
 *  
 *  @ Since 1.2.0
 */
  function get_settings_by_tab( $tab = 'generator' ){
    $current = $this->get_all_options();
    $default_options = $this->option_defaults();
    $return = array();
    foreach($default_options as $key => $val){
      if( $val['tab'] == $tab ){
        $return[$key] = $current[$key];
      }
    }
    return $return;
  }
/**
 *  Create array of positions for a given tab along with a list of settings for each position
 *  
 *  @ Since 1.2.0
 *  @ Updated 1.2.3
 */
  function get_option_positions_by_tab( $tab = 'generator' ){
    $positions = $this->option_positions();
    $return = array();
    if( NULL !== $positions[$tab] ){
      $options = $this->get_options_by_tab( $tab );
      $defaults = $this->option_defaults();
      
      foreach($positions[$tab] as $pos => $info ){
        $return[$pos]['title'] = $info['title'];
        $return[$pos]['description'] = $info['description'];
        $return[$pos]['options'] = array();
      }
      foreach($options as $name){
        $pos = $defaults[$name]['position'];
        $return[ $pos ]['options'][] = $name;
      }
    }
    return $return;
  }
/**
 *  Create array of positions for each widget along with a list of settings for each position
 *  
 *  @ Since 1.2.0
 */
  function get_widget_options_by_position(){
    $default_options = $this->option_defaults();
    $positions = $this->widget_positions();
    $return = array();
    foreach($positions as $key => $val ){
      $return[$key]['title'] = $val;
      $return[$key]['options'] = array();
    }
    foreach($default_options as $key => $val){
      if($val['widget']){
        $return[ $val['position'] ]['options'][] = $key;
      }
    }
    return $return; 
  }
/**
 * Register styles and scripts
 *  
 * @ Since 1.2.3
 *
 */
  function register_style_and_script(){
    wp_register_script($this->wjs,$this->url.'/js/'.$this->wjs.'.js','',$this->ver);
    wp_register_style($this->wcss,$this->url.'/css/'.$this->wcss.'.css','',$this->ver);  
   
    $lightbox = $this->get_option('general_lightbox');
    $prevent = $this->get_option('general_lightbox_no_load');
    
    if( 'fancybox' == $lightbox && !$prevent ){
      wp_register_script( 'fancybox', $this->url.'/js/fancybox/jquery.fancybox-1.3.4.pack.js', '', '1.3.4', true );
      wp_register_style( 'fancybox-stylesheet', $this->url . '/js/fancybox/jquery.fancybox-1.3.4.css', false, '1.3.4', 'screen' );		
    }elseif( 'prettyphoto' == $lightbox && !$prevent ){
      wp_register_script( 'prettyphoto', $this->url.'/js/prettyphoto/js/jquery.prettyPhoto.js', '', '3.1.4', true );
      wp_register_style( 'prettyphoto-stylesheet', $this->url . '/js/prettyphoto/css/prettyPhoto.css', false, '3.1.4', 'screen' );		
    }elseif( 'colorbox' == $lightbox && !$prevent ){
      wp_register_script( 'colorbox', $this->url.'/js/colorbox/jquery.colorbox-min.js', '', '1.3.21', true );
      wp_register_style( 'colorbox-stylesheet', $this->url . '/js/colorbox/colorbox.css', false, '3.1.4', 'screen' );		
    }elseif( 'alpine-fancybox' == $lightbox ){
      wp_register_script( 'fancybox-alpine', $this->url.'/js/fancybox-alpine-safemode/jquery.fancyboxForAlpine-1.3.4.pack.js', '', '1.3.4', true );
      wp_register_style( 'fancybox-alpine-stylesheet', $this->url . '/js/fancybox-alpine-safemode/jquery.fancyboxForAlpine-1.3.4.css', false, '1.3.4', 'screen' );		
    }
    
    // Enable loading the styles and scripts in the header
    $headerload = $this->get_option('general_load_header');
    if( $headerload ){
      if( 'fancybox' == $lightbox && !$prevent ){
        wp_enqueue_script( 'fancybox' );
        wp_enqueue_style( 'fancybox-stylesheet');
      }elseif( 'prettyphoto' == $lightbox && !$prevent ){
        wp_enqueue_script( 'prettyphoto' );
        wp_enqueue_style( 'prettyphoto-stylesheet');
      }elseif( 'colorbox' == $lightbox && !$prevent ){
        wp_enqueue_script( 'colorbox' );
        wp_enqueue_style( 'colorbox-stylesheet' );
      }elseif( 'alpine-fancybox' == $lightbox ){
        wp_enqueue_script( 'fancybox-alpine' );
        wp_enqueue_style( 'fancybox-alpine-stylesheet' );		
      }
      wp_enqueue_style($this->wcss);
      wp_enqueue_script($this->wjs);
    }
  }
/**
 * Enqueue styles and scripts
 *  
 * @ Since 1.2.3
 *
 */
  function enqueue_style_and_script(){
    $headerload = $this->get_option('general_load_header');
    if( !$headerload ){
      // Change web source
      if( $this->options['smugmug_image_link_option'] == "fancybox" ){
        $lightbox = $this->get_option('general_lightbox');
        $prevent = $this->get_option('general_lightbox_no_load');
        if( 'fancybox' == $lightbox && !$prevent ){
          wp_enqueue_script( 'fancybox' );
          wp_enqueue_style( 'fancybox-stylesheet');
        }elseif( 'prettyphoto' == $lightbox && !$prevent ){
          wp_enqueue_script( 'prettyphoto' );
          wp_enqueue_style( 'prettyphoto-stylesheet');
        }elseif( 'colorbox' == $lightbox && !$prevent ){
          wp_enqueue_script( 'colorbox' );
          wp_enqueue_style( 'colorbox-stylesheet' );
        }elseif( 'alpine-fancybox' == $lightbox ){
          wp_enqueue_script( 'fancybox-alpine' );
          wp_enqueue_style( 'fancybox-alpine-stylesheet' );		
        }
      } 
      wp_enqueue_style($this->wcss);
      wp_enqueue_script($this->wjs);
    }
  }
  
/**
 * Options Simple Update for Admin Page
 *  
 * @since 1.2.0
 *
 */
 function SimpleUpdate( $currenttab, $newoptions, $oldoptions ){
    $options = $this->option_defaults();
    $bytab = $this->get_options_by_tab( $currenttab );
    foreach( $bytab as $id){
      $oldoptions[$id] = $this->MenuOptionsValidate( $newoptions[$id],$oldoptions[$id],$options[$id] );
    }
    update_option( $this->settings, $oldoptions);
    return $oldoptions;
  }
/**
  * Function for displaying forms in the widget page
  *
  * @since 1.0.0
  *
  */
  function MenuDisplayCallback($options,$option,$fieldname,$fieldid){
    $default = $option['default'];
    $optionname = $option['name'];
    $optiontitle = $option['title'];
    $optiondescription = $option['description'];
    $fieldtype = $option['type'];
    $value = ( Null !== $options[$optionname] ? $options[$optionname] : $default );
    
     // Output checkbox form field markup
    if ( 'checkbox' == $fieldtype ) {
      ?>
      <input type="checkbox" id="<?php echo $fieldid; ?>" name="<?php echo $fieldname; ?>" <?php checked( $value ); ?> />
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    }
    // Output radio button form field markup
    else if ( 'radio' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options'];
      ?><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label><?php
      foreach ( $valid_options as $valid_option ) {
        ?>
        <input type="radio" name="<?php echo $fieldname; ?>" <?php checked( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" />
        <span class="description"><?php echo $optiondescription; ?></span>
        <?php
      }
    }
    // Output select form field markup
    else if ( 'select' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options']; 
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        foreach ( $valid_options as $valid_option ) {
          ?>
          <option <?php selected( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" ><?php echo $valid_option['title']; ?></option>
          <?php
        }
        ?>
        </select>
        <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } // Output select form field markup
    else if ( 'range' == $fieldtype ) {     
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        for($i = $option['min'];$i <= $option['max']; $i++){
          ?>
          <option <?php selected( $i == $value ); ?> value="<?php echo $i; ?>" ><?php echo $i ?></option>
          <?php
        }
        ?>
        </select>
        <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } 
    // Output text input form field markup
    else if ( 'text' == $fieldtype ) {
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" value="<?php echo ( $value ); ?>" />
      <div class="description"><span class="description"><?php echo $optiondescription; ?></span></div>
      <?php
    } 
    else if ( 'textarea' == $fieldtype ) {
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
      <span class="description"><?php echo esc_textarea( $optiondescription ); ?></span>
      <?php
    }   
    else if ( 'color' == $fieldtype ) {
      $value = ($value?$value:$default);
      ?>    
      <label for="<?php echo $fieldid ?>">
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_color"  value="<?php echo ( $value ); ?>" /><span class="description"><?php echo $optiondescription; ?></span></label>
      <div id="<?php echo $fieldid; ?>_picker" class="AlpinePhotoTiles_color_picker" ></div>
      <?php
    }
  }

/**
 *  Function for displaying forms in the admin page
 *  
 *  @ Since 1.0.0
 */
  function AdminDisplayCallback($options,$option,$fieldname,$fieldid){
    $default = $option['default'];
    $optionname = $option['name'];
    $optiontitle = $option['title'];
    $optiondescription = $option['description'];
    $fieldtype = $option['type'];
    $value = ( Null !== $options[$optionname] ? $options[$optionname] : $default );
    
     // Output checkbox form field markup
    if ( 'checkbox' == $fieldtype ) {
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="checkbox" id="<?php echo $fieldid; ?>" name="<?php echo $fieldname; ?>" <?php checked( $value ); ?> />
      <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    }
    // Output radio button form field markup
    else if ( 'radio' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options'];
      ?><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label><?php
      foreach ( $valid_options as $valid_option ) {
        ?>
        <input type="radio" name="<?php echo $fieldname; ?>" <?php checked( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" />
        <span class="description"><?php echo $optiondescription; ?></span>
        <?php
      }
    }
    // Output select form field markup
    else if ( 'select' == $fieldtype ) {
      $valid_options = array();
      $valid_options = $option['valid_options']; 
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        foreach ( $valid_options as $valid_option ) {
          ?>
          <option <?php selected( $valid_option['name'] == $value ); ?> value="<?php echo $valid_option['name']; ?>" ><?php echo $valid_option['title']; ?></option>
          <?php
        }
        ?>
        </select>
        <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } // Output select form field markup
    else if ( 'range' == $fieldtype ) {     
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
        <select id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" >
        <?php 
        for($i = $option['min'];$i <= $option['max']; $i++){
          ?>
          <option <?php selected( $i == $value ); ?> value="<?php echo $i; ?>" ><?php echo $i ?></option>
          <?php
        }
        ?>
        </select>
        <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } 
    // Output text input form field markup
    else if ( 'text' == $fieldtype ) {
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" value="<?php echo ( $value ); ?>" />
      <span class="description"><?php echo $optiondescription; ?></span>
      <?php
    } 
    else if ( 'textarea' == $fieldtype ) {
      ?>
      <label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label>
      <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
      <span class="description"><?php echo esc_textarea( $optiondescription ); ?></span>
      <?php
    }   
    else if ( 'color' == $fieldtype ) {
      $value = ($value?$value:$default);
      ?>
      <div class="title"><label for="<?php echo $fieldid; ?>"><?php echo $optiontitle ?></label></div>
      <input type="text" id="<?php echo $fieldid ?>" name="<?php echo $fieldname; ?>" class="AlpinePhotoTiles_color"  value="<?php echo ( $value ); ?>" /><span class="description"><?php echo $optiondescription; ?></span></label>
      <div id="<?php echo $fieldid; ?>_picker" class="AlpinePhotoTiles_color_picker" ></div>
      <?php
    }
  }


/**
 * Options Validate Pseudo-Callback
 *
 * @ Since 1.0.0
 * @ Updated 1.2.3
 */
  function MenuOptionsValidate( $newinput, $oldinput, $optiondetails ) {
      $valid_input = $oldinput;

      // Validate checkbox fields
      if ( 'checkbox' == $optiondetails['type'] ) {
        // If input value is set and is true, return true; otherwise return false
        $valid_input = ( ( isset( $newinput ) && true == $newinput ) ? true : false );
      }
      // Validate radio button fields
      else if ( 'radio' == $optiondetails['type'] ) {
        // Get the list of valid options
        $valid_options = $optiondetails['valid_options'];
        // Only update setting if input value is in the list of valid options
        $valid_input = ( array_key_exists( $newinput, $valid_options ) ? $newinput : $valid_input );
      }
      // Validate select fields
      else if ( 'select' == $optiondetails['type'] || 'select-trigger' == $optiondetails['type']) {
        // Get the list of valid options
        $valid_options = $optiondetails['valid_options'];
        // Only update setting if input value is in the list of valid options
        $valid_input = ( array_key_exists( $newinput, $valid_options ) ? $newinput : $valid_input );
      }
      else if ( 'range' == $optiondetails['type'] ) {
        // Only update setting if input value is in the list of valid options
        $valid_input = ( ($newinput>=$optiondetails['min'] && $newinput<=$optiondetails['max']) ? $newinput : $valid_input );
      }    
      // Validate text input and textarea fields
      else if ( ( 'text' == $optiondetails['type'] || 'textarea' == $optiondetails['type'] || 'image-upload' == $optiondetails['type']) ) {
        $valid_input = strip_tags( $newinput );
        
        // Validate no-HTML content
        // nospaces option offers additional filters
        if ( 'nospaces' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          
          // Remove specified character(s)
          if(Null !== $optiondetails['remove']){
            if( is_array($optiondetails['remove']) ){
              foreach( $optiondetails['remove'] as $remove ){
                $valid_input = str_replace($remove,'',$valid_input);
              }
            }else{
              $valid_input = str_replace($optiondetails['remove'],'',$valid_input);
            }
          }
          // Switch or encode characters
          if( is_array( $optiondetails['encode'] ) ){
            foreach( $optiondetails['encode'] as $find=>$replace ){
              $valid_input = str_replace($find,$replace,$valid_input);
            }
          }
          // Replace spaces with provided character or just remove spaces
          if(Null !== $optiondetails['replace']){
            $valid_input = str_replace(array('  ',' '),$optiondetails['replace'],$valid_input);
          }else{
            $valid_input = str_replace(' ','',$valid_input);
          }
        }          
        // Check if numeric
        if ( 'numeric' == $optiondetails['sanitize'] && is_numeric( wp_filter_nohtml_kses( $newinput ) ) ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          if( NULL !== $optiondetails['min'] && $valid_input<$optiondetails['min']){
            $valid_input = $optiondetails['min'];
          }
          if( NULL !== $optiondetails['max'] && $valid_input>$optiondetails['max']){
            $valid_input = $optiondetails['max'];
          }
        }
        if ( 'int' == $optiondetails['sanitize'] && is_numeric( wp_filter_nohtml_kses( $newinput ) ) ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = round( wp_filter_nohtml_kses( $newinput ) );
          if( NULL !== $optiondetails['min'] && $valid_input<$optiondetails['min']){
            $valid_input = $optiondetails['min'];
          }
          if( NULL !== $optiondetails['max'] && $valid_input>$optiondetails['max']){
            $valid_input = $optiondetails['max'];
          }
        }         
        if ( 'tag' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          $valid_input = str_replace(' ','-',$valid_input);
        }            
        // Validate no-HTML content
        if ( 'nohtml' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_nohtml_kses filter
          $valid_input = wp_filter_nohtml_kses( $newinput );
          $valid_input = str_replace(' ','',$valid_input);
        }
        // Validate HTML content
        if ( 'html' == $optiondetails['sanitize'] ) {
          // Pass input data through the wp_filter_kses filter using allowed post tags
          $valid_input = wp_kses_post($newinput );
        }
        // Validate URL address
        if( 'url' == $optiondetails['sanitize'] ){
          $valid_input = esc_url( $newinput );
        }
        // Validate URL address
        if( 'css' == $optiondetails['sanitize'] ){
          $valid_input = wp_htmledit_pre( stripslashes( $newinput ) );
        }      
      }else if( 'wp-textarea' == $optiondetails['type'] ){
          // Text area filter
          $valid_input = wp_kses_post( force_balance_tags($newinput) );
      }
      elseif( 'color' == $optiondetails['type'] ){
        $value =  wp_filter_nohtml_kses( $newinput );
        if( '#' == $value ){
          $valid_input = '';
        }else{
          $valid_input = $value;
        }
      }
      return $valid_input;
  }
  
/**
 * Get current settings page tab
 *  
 * @since 1.2.0
 *
 */
  function get_current_tab( $current = 'general' ) {
      if ( isset ( $_GET['tab'] ) ) :
          $current = $_GET['tab'];
      else:
          $current = 'general';
      endif;
    return $current;
  }
/**
 * Create shortcode based on given options
 *  
 * @since 1.1.0
 *
 */
  function generate_shortcode( $options, $optiondetails ){
    $short = '['.$this->short;
    $trigger = '';
    foreach( $options as $key=>$value ){
      if($value && $optiondetails[$key]['short']){
        if( $optiondetails[$key]['child'] && $optiondetails[$key]['hidden'] ){
          $hidden = @explode(' ',$optiondetails[$key]['hidden']);
          if( !in_array( $options[ $optiondetails[$key]['child'] ] ,$hidden) ){
            $short .= ' '.$optiondetails[$key]['short'].'="'.$value.'"';
          }
        }else{
          $short .= ' '.$optiondetails[$key]['short'].'="'.$value.'"';
        }
      }
    }
    $short .= ']';
    
    return $short;
  }
/**
 * Define Settings Page Tab Markup
 *  
 * @since 1.1.0
 * @link`http://www.onedesigns.com/tutorials/separate-multiple-theme-options-pages-using-tabs	Daniel Tara
 *
 */
  function admin_options_page_tabs( $current = 'general' ) {

    $tabs = $this->settings_page_tabs();
    $links = array();
    
    foreach( $tabs as $tab ) :
      $tabname = $tab['name'];
      $tabtitle = $tab['title'];
      if ( $tabname == $current ) :
          $links[] = "<a class='nav-tab nav-tab-active' href='?page=".$this->settings."&tab=$tabname'>$tabtitle</a>";
      else :
          $links[] = "<a class='nav-tab' href='?page=".$this->settings."&tab=$tabname'>$tabtitle</a>";
      endif;
    endforeach;

    echo '<div class="AlpinePhotoTiles-title"><div class="icon32 icon-alpine"><br></div><h2>'.$this->name.'</h2></div>';
    echo '<div class="AlpinePhotoTiles-menu"><h2 class="nav-tab-wrapper">';
    foreach ( $links as $link )
        echo $link;
    echo '</h2></div>';
  }
/**
 * Function for printing general settings page
 *  
 * @since 1.2.0
 *
 */
  function display_general(){ 
    ?>
    <div class="AlpinePhotoTiles-general">
      <h3><?php _e("Thank you for downloading the "); echo $this->name; _e(", a WordPress plugin by the Alpine Press.");?></h3>
      <p><?php _e("On the 'Shortcode Generator' tab you will find an easy to use interface that will help you create shortcodes. These shortcodes make it simple to insert the PhotoTile plugin into posts and pages.");?></p>
      <p><?php _e("The 'Plugin Settings' tab provides additional back-end options.");?></p>
      <p><?php _e("Finally, I am a one man programming team and so if you notice any errors or places for improvement, please let me know."); ?></p>
      <p><?php _e('If you liked this plugin, try out some of the other plugins by ') ?><a href="http://thealpinepress.com/category/plugins/" target="_blank">the Alpine Press</a><?php _e(' and please rate us at ') ?><a href="<?php echo $this->wplink;?>" target="_blank">WordPress.org</a>.</p>
      <br>
      <h3><?php _e('Try the other free plugins in the Alpine PhotoTile Series:');?></h3>
      <?php if( is_array($this->plugins) ){
        foreach($this->plugins as $each){
          ?><a href="http://wordpress.org/extend/plugins/alpine-photo-tile-for-<?php echo $each;?>/" target="_blank"><img class="image-icon" src="<?php echo $this->url;?>/css/images/for-<?php echo $each;?>.png" style="width:100px;"></a><?php
        }
      }?>

      <div class="help-link"><p><?php _e('Need Help? Visit ') ?><a href="<?php echo $this->info; ?>" target="_blank">the Alpine Press</a><?php _e(' for more about this plugin.') ?></p></div>
      </p>
    </div>
    <?php
  }
/**
 * Function for printing shortcode preview page
 *  
 * @since 1.2.0
 *
 */
  function display_preview(){ 
    $fieldid = "shortcode-preview";
    $value = '';
    $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );

    if( $submitted ){
      $value = wp_kses_post( str_replace('\"','"', $_POST['shortcode-preview']) );
    }
    ?>
      <div class="AlpinePhotoTiles-preview" style="border-bottom: 1px solid #DDDDDD;margin-bottom:20px;">
        <form action="" method="post">
          <input type="hidden" name="hidden" value="Y">
          <div>
          <h4><?php _e('Paste shortcode and click Preview');?></h4>
          <textarea id="<?php echo $fieldid ?>" name="<?php echo $fieldid; ?>" class="AlpinePhotoTiles_textarea" ><?php echo $value; ?></textarea><br>
          <span class="description"><?php echo esc_textarea( $optiondescription ); ?></span>
          <input name="<?php echo $this->settings;?>_preview [submit-preview]" type="submit" class="button-primary" value="Preview" />
          </div>
        </form>
        <br style="clear:both">
      </div>
    <?php 
    
    echo do_shortcode($value);
    
  }
/**
 * Function for printing options page
 *  
 * @ Since 1.1.0
 * @ Updated 1.2.3
 *
 */
  function display_options_form($options,$currenttab,$short){

    $defaults = $this->option_defaults();
    $positions = $this->get_option_positions_by_tab( $currenttab );
    
    if( 'generator' == $currenttab ) { 
      echo '<input name="'. $this->settings.'_'.$currenttab .'[submit-'. $currenttab .']" type="submit" class="button-primary topbutton" value="Generate Shortcode" /><br> ';
      if($short){
        echo '<div id="'.$this->settings.'-shortcode" style="position:relative;clear:both;margin-bottom:20px;" ><div class="announcement" style="margin:0 0 10px 0;"> Now, copy (Crtl+C) and paste (Crtl+V) the following shortcode into a page or post. </div>';
        echo '<div><textarea class="auto_select">'.$short.'</textarea></div></div>';
      }
    }
    if( count($positions) ){
      foreach( $positions as $position=>$positionsinfo){
        echo '<div class="'. $position .'">'; 
          if( $positionsinfo['title'] ){ echo '<h4>'. $positionsinfo['title'].'</h4>'; } 
          if( $positionsinfo['description'] ){ echo '<div style="margin-bottom:15px;"><span class="description" >'. $positionsinfo['description'].'</span></div>'; } 
          echo '<table class="form-table">';
            echo '<tbody>';
              if( count($positionsinfo['options']) ){
                foreach( $positionsinfo['options'] as $optionname ){
                  $option = $defaults[$optionname];
                  $fieldname = ( $option['name'] );
                  $fieldid = ( $option['name'] );

                  if( $option['hidden-option'] && $option['check'] ){
                    $show = $this->get_option( $option['check'] );
                    if( !$show ){ continue; }
                  }
                  
                  if($option['parent']){
                    $class = $option['parent'];
                  }elseif($option['child']){
                    $class =($option['child']);
                  }else{
                    $class = ('unlinked');
                  }
                  $trigger = ($option['trigger']?('data-trigger="'.(($option['trigger'])).'"'):'');
                  $hidden = ($option['hidden']?' '.$option['hidden']:'');
                  
                  if( 'generator' == $currenttab ){                  
                    echo '<tr valign="top"> <td class="'.$class.' '.$hidden.'" '.$trigger.'>';
                      $this->MenuDisplayCallback($options,$option,$fieldname,$fieldid);
                    echo '</td></tr>';   
                  }else{
                    echo '<tr valign="top"><td class="'.$class.' '.$hidden.'" '.$trigger.'>';
                      $this->AdminDisplayCallback($options,$option,$fieldname,$fieldid);
                    echo '</td></tr>';   
                  }       
                }
              }
            echo '</tbody>';
          echo '</table>';
        echo '</div>';
      }
    }
    echo '<div class="help-link"><span>'. __('Need Help? Visit ') .'<a href="' . $this->info . '" target="_blank">the Alpine Press</a>'. __(" for more about this plugin.") .'</span></div>';
    
    if( 'generator' == $currenttab ) {
      echo '<input name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" value="Generate Shortcode" />';
    }elseif( 'plugin-settings' == $currenttab ){
      echo '<input name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" value="Save Settings" />';
      echo '<input name="'.$this->settings.'_'.$currenttab .'[submit-'. $currenttab.']" type="submit" class="button-primary" style="margin-top:15px;" value="Delete Current Cache" />';
    }

  }
  
  
  
  
/**
 * Functions for caching results and clearing cache
 *  
 * @since 1.1.0
 *
 */
  public function setCacheDir($val) {  $this->cacheDir = $val; }  
  public function setExpiryInterval($val) {  $this->expiryInterval = $val; }  
  public function getExpiryInterval($val) {  return (int)$this->expiryInterval; }
  
  public function cacheExists($key) {  
    $filename_cache = $this->cacheDir . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->cacheDir . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info)) {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->expiryInterval; //Last update time of the cache file  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time) {//Compare last updated and current time  
        return true;  
      }  
    }
    return false;  
  } 

  public function getCache($key)  {  
    $filename_cache = $this->cacheDir . '/' . $key . '.cache'; //Cache filename  
    $filename_info = $this->cacheDir . '/' . $key . '.info'; //Cache info  
  
    if (file_exists($filename_cache) && file_exists($filename_info))  {  
      $cache_time = file_get_contents ($filename_info) + (int)$this->expiryInterval; //Last update time of the cache file  
      $time = time(); //Current Time  

      $expiry_time = (int)$time; //Expiry time for the cache  

      if ((int)$cache_time >= (int)$expiry_time){ //Compare last updated and current time 
        return file_get_contents ($filename_cache);   //Get contents from file  
      }  
    }
    return null;  
  }  

  public function putCache($key, $content) {  
    $time = time(); //Current Time  
    
    if ( ! file_exists($this->cacheDir) ){  
      @mkdir($this->cacheDir);  
      $cleaning_info = $this->cacheDir . '/cleaning.info'; //Cache info 
      @file_put_contents ($cleaning_info , $time); // save the time of last cache update  
    }
    
    if ( file_exists($this->cacheDir) && is_dir($this->cacheDir) ){ 
      $dir = $this->cacheDir . '/';
      $filename_cache = $dir . $key . '.cache'; //Cache filename  
      $filename_info = $dir . $key . '.info'; //Cache info  
    
      @file_put_contents($filename_cache ,  $content); // save the content  
      @file_put_contents($filename_info , $time); // save the time of last cache update  
    }
  }
  
  public function clearAllCache() {
    $dir = $this->cacheDir . '/';
    if(is_dir($dir)){
      $opendir = @opendir($dir);
      while(false !== ($file = readdir($opendir))) {
        if($file != "." && $file != "..") {
          if(file_exists($dir.$file)) {
            $file_array = @explode('.',$file);
            $file_type = @array_pop( $file_array );
            // only remove cache or info files
            if( 'cache' == $file_type || 'info' == $file_type){
              @chmod($dir.$file, 0777);
              @unlink($dir.$file);
            }
          }
          /*elseif(is_dir($dir.$file)) {
            @chmod($dir.$file, 0777);
            @chdir('.');
            @destroy($dir.$file.'/');
            @rmdir($dir.$file);
          }*/
        }
      }
      @closedir($opendir);
    }
  }
  
  public function cleanCache() {
    $cleaning_info = $this->cacheDir . '/cleaning.info'; //Cache info     
    if (file_exists($cleaning_info))  {  
      $cache_time = file_get_contents ($cleaning_info) + (int)$this->cleaningInterval; //Last update time of the cache cleaning  
      $time = time(); //Current Time  
      $expiry_time = (int)$time; //Expiry time for the cache  
      if ((int)$cache_time < (int)$expiry_time){ //Compare last updated and current time     
        // Clean old files
        $dir = $this->cacheDir . '/';
        if(is_dir($dir)){
          $opendir = @opendir($dir);
          while(false !== ($file = readdir($opendir))) {                            
            if($file != "." && $file != "..") {
              if(is_dir($dir.$file)) {
                //@chmod($dir.$file, 0777);
                //@chdir('.');
                //@destroy($dir.$file.'/');
                //@rmdir($dir.$file);
              }
              elseif(file_exists($dir.$file)) {
                $file_array = @explode('.',$file);
                $file_type = @array_pop( $file_array );
                $file_key = @implode( $file_array );
                if( $file_type && $file_key && 'info' == $file_type){
                  $filename_cache = $dir . $file_key . '.cache'; //Cache filename  
                  $filename_info = $dir . $file_key . '.info'; //Cache info   
                  if (file_exists($filename_cache) && file_exists($filename_info)) {  
                    $cache_time = file_get_contents ($filename_info) + (int)$this->cleaningInterval; //Last update time of the cache file  
                    $expiry_time = (int)$time; //Expiry time for the cache  
                    if ((int)$cache_time < (int)$expiry_time) {//Compare last updated and current time  
                      @chmod($filename_cache, 0777);
                      @unlink($filename_cache);
                      @chmod($filename_info, 0777);
                      @unlink($filename_info);
                    }  
                  }
                }
              }
            }
          }
          @closedir($opendir);
        }
        @file_put_contents ($cleaning_info , $time); // save the time of last cache cleaning        
      }
    }
  } 
  
  
  /////////////////////////////////////////////////////////////
  ///////// Source-specific functions below this line /////////
  /////////////////////////////////////////////////////////////  
  
  /**
   * Alpine PhotoTile: Options Page
   *
   * @since 1.1.1
   *
   */
  function build_settings_page(){
    $optiondetails = $this->option_defaults();
    $currenttab = $this->get_current_tab();
    
    echo '<div class="wrap AlpinePhotoTiles_settings_wrap">';
    $this->admin_options_page_tabs( $currenttab );

      echo '<div class="AlpinePhotoTiles-container '.$this->domain.'">';
      
      if( 'general' == $currenttab ){
        $this->display_general();
      }elseif( 'preview' == $currenttab ){
        $this->display_preview();
      }else{
        $options = $this->get_all_options();     
        $settings_section = $this->id . '_' . $currenttab . '_tab';
        $submitted = ( ( isset($_POST[ "hidden" ]) && ($_POST[ "hidden" ]=="Y") ) ? true : false );

        if( $submitted ){
          $options = $this->SimpleUpdate( $currenttab, $_POST, $options );
          if( 'generator' == $currenttab ) {
            $short = $this->generate_shortcode( $options, $optiondetails );
          }
        }
        echo '<div class="AlpinePhotoTiles-'.$currenttab.'">';
          if( $_POST[$this->settings.'_'.$currenttab]['submit-'.$currenttab] == 'Delete Current Cache' ){
            $this->clearAllCache();
            echo '<div class="announcement">'.__("Cache Cleared").'</div>';
          }
          elseif( $_POST[$this->settings.'_'.$currenttab]['submit-'.$currenttab] == 'Save Settings' ){
            $this->clearAllCache();
            echo '<div class="announcement">'.__("Settings Saved").'</div>';
          }
          echo '<form action="" method="post">';
            echo '<input type="hidden" name="hidden" value="Y">';
            $this->display_options_form($options,$currenttab,$short);
          echo '</form>';
        echo '</div>';
      }
      echo '</div>'; // Close Container
    echo '</div>'; // Close wrap
  }
  
}



?>
