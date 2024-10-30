<?php
/*
Plugin Name: Location Report
Plugin URI:  http://pitufa.at/current_location_and_route_kml
Description: Ideal for travel blogs, this plugin provides the [locationreport ...] shortcode that lets you update your current location. It generates two kml files. The first has a placemark of your current location and the second one shows the route along all your location reports. Those kml files can be displayed by one of the many available map plugins (e.g., OpenStreetMap, Leaflet Map, or Flexible Map). Note, this plugin itself does NOT provide a map. However, it provides a simple, universal (no GUI interaction necessary, so you can report your position even when posting by email), portable (no db, just kml files) and modular way of position reporting and together with a map plugin you can show your latest position and/or your travel route in a map widget, post or page.
Version:     1.1.3
Author:      Christian Feldbauer
Author URI:  http://www.pitufa.at
Text Domain: locationreport
Domain Path: /languages
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );

require('libkml.php');

define("LOCATIONREPORT_VER", "V1.1.3");
define('LOCATIONREPORT_PLUGIN_URL', plugins_url().'/'.basename( dirname( __FILE__ )).'/');

define('LOCATIONREPORT_DIR_PATH', dirname( __FILE__ ));
define('LOCATIONREPORT_TEMPLATES_PATH', path_join(LOCATIONREPORT_DIR_PATH,'templates'));
define('LOCATIONREPORT_SYMLINKS_PATH', path_join(LOCATIONREPORT_DIR_PATH,'symlinks'));


function locationreport_load_plugin_textdomain() {
    load_plugin_textdomain( 'locationreport', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'locationreport_load_plugin_textdomain' );

// enable shortcodes in widgets (e.g. to make your own map widget using a simple text widget...) 
add_filter('widget_text','do_shortcode');

//Plugin Activation
function locationreport_activate()
{
  $upload_dir_arr = wp_upload_dir();
  $kmldir = path_join( $upload_dir_arr['basedir'], 'locationreport');
  if( !is_dir($kmldir) )
  {
    mkdir( $kmldir);
  }
  
  $fn_empty = path_join( LOCATIONREPORT_TEMPLATES_PATH, 'empty.kml');
  
  $fn_route = path_join( $kmldir, 'route.kml');
  $link_route_html = path_join( $kmldir, 'route.html');
  $link_route = path_join( LOCATIONREPORT_SYMLINKS_PATH, 'route.kml.txt');
  if( !file_exists($fn_route) )
    copy( $fn_empty, $fn_route);
  if( !file_exists($link_route_html) )
    symlink( $fn_route, $link_route_html);
  if( !file_exists($link_route) )
    symlink( $fn_route, $link_route);
  
  $fn_latest = path_join( $kmldir, 'latest.kml');
  $link_latest_html = path_join( $kmldir, 'latest.html');
  $link_latest = path_join( LOCATIONREPORT_SYMLINKS_PATH, 'latest.kml.txt');
  if( !file_exists($fn_latest) )
    copy( $fn_empty, $fn_latest);
  if( !file_exists($link_latest_html) )
    symlink( $fn_latest, $link_latest_html);    
  if( !file_exists($link_latest) )
    symlink( $fn_latest, $link_latest);
  
  $fn_style = path_join( $kmldir, 'style.kml');
  $link_style = path_join( LOCATIONREPORT_SYMLINKS_PATH, 'style.kml.txt');
  if( !file_exists($fn_style) )
    copy( $fn_empty, $fn_style); 
  if( !file_exists($link_style) )
    symlink( $fn_style, $link_style); 
}
register_activation_hook( __FILE__, 'locationreport_activate' );

// [locationreport lat=... lon=...]
add_shortcode( 'locationreport', 'locationreport_func' );
function locationreport_func( $atts )
{ 
  if( !is_array($atts) )
        $atts = array();
        
  if( array_key_exists ( 'lat' ,  $atts) && 
      array_key_exists ( 'lon' ,  $atts) )
  {
    if( array_key_exists ( 'show' ,  $atts) )
    {
      $lat_dec = locationreport_coord_to_dec_deg( $atts['lat']);
      $lon_dec = locationreport_coord_to_dec_deg( $atts['lon']);
           
      $locationreport_str1 = __( 'Position reported on', 'locationreport');
      $locationreport_str2 = __( 'at', 'locationreport');
      
      $postdatetime = get_the_time( 'U' );
      $date_fmt_str = "Y-m-d";
      $date_str = date($date_fmt_str, $postdatetime);
      $time_fmt_str = 'H:i';
      $time_str = date($time_fmt_str, $postdatetime);
      $repl = '<p><em>' . $locationreport_str1 . ' ' . $date_str . ' ' . $locationreport_str2 . ' ' . $time_str . ' UTC: <br>' . locationreport_lat_dec_deg_to_min_str($lat_dec) . ', ' . locationreport_lon_dec_deg_to_min_str($lon_dec) . '</em></p>';
    } else {
      $repl = '';
    }
  } else {
    $repl = '<p><em>(' . __( 'Note, lat and lon attributes are needed in the locationreport shortcode.', 'locationreport' ) . ')</em></p>';
  }  
  return $repl;
}

// all kml magic happens after post has been saved in db
function locationreport_insert_post( $post_ID, $post )
{ 
  if( $post->post_status === 'publish' || $post->post_status === 'private' )
  {  
    $content = $post->post_content;

    $upload_dir_arr = wp_upload_dir();
    $kmldir = path_join( $upload_dir_arr['basedir'], 'locationreport');

    $pattern = get_shortcode_regex( ); 
    preg_match_all('/'.$pattern.'/s', $content, $matches);
    
    $tag_index = array_search( 'locationreport', $matches[2]);
    if ( $tag_index !== false )
    { 
      $att_arr = shortcode_parse_atts( stripslashes($matches[3][$tag_index]));
      if( !is_array($att_arr) )
        $att_arr = array();
      
      if( array_key_exists ( 'lat'  , $att_arr) && 
          array_key_exists ( 'lon'  , $att_arr) &&
         !array_key_exists ( 'file' , $att_arr) ) // editing archived or alternative kml files is not supported yet
      { 
        $lat_dec = locationreport_coord_to_dec_deg( $att_arr['lat']);
        $lon_dec = locationreport_coord_to_dec_deg( $att_arr['lon']);
         
        $locationreport_str1 = __( 'Position reported on', 'locationreport');
        $locationreport_str2 = __( 'at', 'locationreport');
        $when_fmt_str = "Y-m-d\TH:i:sP";
        $date_fmt_str = "Y-m-d";
        $time_fmt_str = 'H:i';
        $tstamp = strtotime($post->post_date_gmt);
        $when_str = date($when_fmt_str, $tstamp);
        $date_str = date($date_fmt_str, $tstamp);
        $time_str = date($time_fmt_str, $tstamp);
        $repl = '<p><em>' . $locationreport_str1 . ' ' . $date_str . ' ' . $locationreport_str2 . ' ' . $time_str . ' UTC: <br>' . locationreport_lat_dec_deg_to_min_str($lat_dec) . ', ' . locationreport_lon_dec_deg_to_min_str($lon_dec) . '</em></p>';
        
        // remove shortcode from content and replace with location report box (for kml placemark description only!)
        $content = str_replace( $matches[0][$tag_index], $repl, $content );
        
        $fn_route = path_join( $kmldir, 'route.kml');
        $fn_latest = path_join( $kmldir, 'latest.kml');
        $fn_empty = path_join( $kmldir, 'style.kml'); 
        
        $title = get_post_field('post_title', $post, 'raw');
        
        locationreport_process_kml_files( $fn_route, $fn_latest, $fn_empty, $lat_dec, $lon_dec, apply_filters('the_content', $content), $title, $post_ID, $when_str);
      }
    } 
  }  
}
add_action( 'wp_insert_post', 'locationreport_insert_post', 99, 2 );

function locationreport_before_insert( $data , $postarr )
{ 
  if( $data['post_status'] === 'publish' || $data['post_status'] === 'private' )
  {
    $post_ID = $postarr['ID'];
    
    $content = $data['post_content'];

    $upload_dir_arr = wp_upload_dir();
    $kmldir = path_join( $upload_dir_arr['basedir'], 'locationreport');

    $pattern = get_shortcode_regex( ); 
    preg_match_all('/'.$pattern.'/s', $content, $matches);
    
    $tag_index = array_search( 'locationreport', $matches[2]);
    if ( $tag_index !== false )
    { 
      $att_arr = shortcode_parse_atts( stripslashes($matches[3][$tag_index]));
      if( !is_array($att_arr) )
        $att_arr = array();

      // removing placemark from kml and shortcode from post
      if( !array_key_exists ( 'lat'  , $att_arr) && 
          !array_key_exists ( 'lon'  , $att_arr) )
      {
        if ( array_key_exists ( 'file' , $att_arr) ) // removing from archived or alternative kml files is already supported
          locationreport_remove_kml_placemark( path_join( $kmldir, $att_arr['file'] ), $post_ID);
        else
          locationreport_remove_kml_placemark( path_join( $kmldir, 'route.kml'), $post_ID, path_join( $kmldir, 'latest.kml') );
      
        // remove shortcode from content
        $data['post_content'] = str_replace( $matches[0][$tag_index], '', $content );
      }
    }
  }  
  return $data;
}
add_filter( 'wp_insert_post_data', 'locationreport_before_insert', '91', 2 );

function locationreport_process_kml_files( $fn_route, $fn_latest, $fn_empty, $lat, $lon, $content, $title, $id, $date)
{
  $kml_str = file_get_contents($fn_route);
  $kml = KML::createFromText($kml_str);    
  $doc = $kml->getFeature();
  $ftrs = $doc->getFeatures();
  
  // new lat/lon
  $coordinates = new libKML\Coordinates();
  $coordinates->setLongitude( $lon );
  $coordinates->setLatitude( $lat );
  $coordinates->setAltitude( 0 );
  
  $timestamp = new libKML\TimeStamp();
  $timestamp->setWhen( $date );
  
  $update = false;
  $points = array();
  $times = array();
  $point_placemarks = array();
  foreach( $ftrs as $ftr )
  {
    if( is_a( $ftr, 'libKML\Placemark') )
    { 
      $ftr->setStyleUrl('#locationreportRouteStyle');
      $geom = $ftr->getGeometry();
      if( is_a( $geom, 'libKML\Point') )
      {
        $id_ = $ftr->getId(); 
        if( strcmp( $id_, $id)==0 ) 
        { // placemark exists already, so do not add a new point but just update this one!
          $update = true;
          $geom->setCoordinates($coordinates);
          $ftr->setName($title);
          $ftr->setDescription($content);
          $ftr->setTimePrimitive($timestamp);
        }
        $points[] = $geom->getCoordinates();
        $times[] = strtotime( $ftr->getTimePrimitive()->getWhen() );
        $point_placemarks[] = $ftr;
      } elseif( is_a( $geom, 'libKML\LineString') )
      {
        $line = $geom;
      }
    }      
  }
  
  if( !$update ) 
  { // create new placemark
    $point = new libKML\Point();
    $point->setCoordinates($coordinates);
    $placemark = new libKML\Placemark();
    $placemark->setId($id);
    $placemark->setName($title);
    $placemark->setDescription($content);
    $placemark->setTimePrimitive($timestamp);
    $placemark->setStyleUrl('#locationreportRouteStyle');
    $placemark->setGeometry($point);
    $doc->addFeature($placemark);
    
    // add new point to line
    $points[] = $coordinates;
    $point_placemarks[] = $placemark;
    $times[] = strtotime( $date );
  }
  array_multisort($times, $points, $point_placemarks);

  if( !isset($line) )
  { // create LineString if at least 2 points
    if( count($points)>1 )
    {
      $line = new libKML\LineString();
      $line->setCoordinates( $points);
      $line->setTessellate(1);
      $placemark = new libKML\Placemark();
      $placemark->setStyleUrl('#locationreportRouteStyle');
      $placemark->setGeometry($line);
      $doc->addFeature($placemark);
    }
  }
  if( isset($line) )
    $line->setCoordinates( $points);
  
  // style change of last placemark
  $latest_pl = end( $point_placemarks );
  $latest_pl->setStyleUrl('#locationreportLatestStyle');
  
  // kml file with single point placemark: latest.kml from style.kml template
  $kml_str = file_get_contents($fn_empty);
  $kml2 = KML::createFromText($kml_str);    
  $doc2 = $kml2->getFeature();
  $styles = $doc2->getAllStyles();

  $doc2->addFeature($latest_pl);
  
  $kml_str_tmp = file_get_contents($fn_latest);
  $len_pre = strlen($kml_str_tmp);

  $kml_str = $kml2->__toString();
  $len_now = strlen($kml_str);

  if( $len_now < $len_pre - 5000 ) // no padding, restart growing...
    $len_padded = $len_now;
  else
    $len_padded = $len_pre + 100;

  $kml_str = str_pad( $kml_str, $len_padded);
  
  file_put_contents( $fn_latest, $kml_str); // write latest.kml
  
  // possible style update through style.kml
  $doc->clearStyleSelectors();  
  foreach( $styles as $st )
    $doc->addStyleSelector($st); // copy template styles into route.kml
    
  file_put_contents( $fn_route, $kml->__toString()); // write route.kml  
}

function locationreport_remove_kml_placemark( $fn_kml, $id, $fn_latest='' )
{
  $kml_str = file_get_contents($fn_kml);
  $kml = KML::createFromText($kml_str);    
  $doc = $kml->getFeature();
  $ftrs = $doc->getFeatures();

  $found = false;
  $ind = 0;
  $points = array();
  $times = array();
  $point_placemarks = array();  
  foreach( $ftrs as $ftr )
  {
    if( is_a( $ftr, 'libKML\Placemark') )
    { 
      $geom = $ftr->getGeometry();
      if( is_a( $geom, 'libKML\Point') )
      {
        $id_ = $ftr->getId(); 
        if( strcmp( $id_, $id)==0 ) 
          $found = $ind;
        else
        {
          $points[] = $geom->getCoordinates();
          $times[] = strtotime( $ftr->getTimePrimitive()->getWhen() );
          $point_placemarks[] = $ftr;
        }
      } elseif( is_a( $geom, 'libKML\LineString') )
      {
        $line = $geom;
        $linekey = $ind;
      }
    }
    $ind++;
  }
  if( $found !== false)
  {
    unset( $ftrs[$found] ); // remove point placemark
    array_multisort($times, $points, $point_placemarks);
    
    if( isset($line) )
    { 
      if( count($points)>1 )
        $line->setCoordinates( $points ); // update linestring
      else
        unset( $ftrs[$linekey] ); // remove linestring
    }
    
    // style change of last placemark
    if( !empty($point_placemarks) )
    {
      $latest_pl = end( $point_placemarks );
      $latest_pl->setStyleUrl('#locationreportLatestStyle');
    }
    $doc->setFeatures( $ftrs );
    file_put_contents( $fn_kml, $kml->__toString()); // write kml file
    
    if( !empty($fn_latest) ) 
    { 
      $kml_str = file_get_contents($fn_latest);
      $kml2 = KML::createFromText($kml_str);    
      $doc2 = $kml2->getFeature();
      $doc2->clearFeatures();
      if( !empty($point_placemarks) )
        $doc2->addFeature($latest_pl);
      
      $kml_str_tmp = file_get_contents($fn_latest);
      $len_pre = strlen($kml_str_tmp);

      $kml_str = $kml2->__toString();
      $len_now = strlen($kml_str);

      if( $len_now < $len_pre - 5000 ) // no padding, restart growing...
        $len_padded = $len_now;
      else
        $len_padded = $len_pre + 100;

      str_pad( $kml_str, $len_padded);
      file_put_contents( $fn_latest, $kml_str); // write latest.kml

    }    
  }
}

function locationreport_coord_to_dec_deg( $val)
{
  if( is_numeric($val) )
    $dec = $val;
  else
  {
    $n = preg_match_all('/(\d+(\.\d+)?)/', $val, $matches);
    $nums = $matches[1];
    $dec = 0;
    if( $n > 0 )
    {
      $dec += $nums[0];
      if( $n > 1 )
        $dec += $nums[1]/60;
    }
    if( strpos( strtoupper($val) , 'S') !== false )
      $dec *= -1;
    if( strpos( strtoupper($val) , 'W') !== false )
      $dec *= -1;
  }
  return $dec;
}

function locationreport_lat_dec_deg_to_min_str($coord)
{
  $isnorth = $coord>=0;
  $coord = abs($coord);
  $deg = floor($coord);
  $min = ($coord-$deg)*60;

  return sprintf("%02d°%05.2f'%s", $deg, $min, $isnorth ? 'N' : 'S');
}

function locationreport_lon_dec_deg_to_min_str($coord)
{
  $iseast = $coord>=0;
  $coord = abs($coord);
  $deg = floor($coord);
  $min = ($coord-$deg)*60;

  return sprintf("%03d°%05.2f'%s", $deg, $min, $iseast ? 'E' : 'W');
}


function locationreport_archive_shortcode( $post_id, $archive )
{ 
  $post = get_post( $post_id );
  if ($post)
  {
    $content = $post->post_content;
    $pattern = get_shortcode_regex( ); 
    preg_match_all('/'.$pattern.'/s', $content, $matches);
    $tag_index = array_search( 'locationreport', $matches[2]);
    if ( $tag_index !== false )
    {
      $repl = str_replace( 'locationreport', 'locationreport file="'.$archive.'"', $matches[0][$tag_index] );         
      $post->post_content = str_replace( $matches[0][$tag_index], $repl, $content );
      wp_update_post( $post );
    }
  }  
}    

function locationreport_unarchive_shortcode( $post_id )
{ 
  $post = get_post( $post_id );
  if ($post)
  {
    $content = $post->post_content;
    $pattern = get_shortcode_regex( ); 
    preg_match_all('/'.$pattern.'/s', $content, $matches);
    $tag_index = array_search( 'locationreport', $matches[2]);
    if ( $tag_index !== false )
    {
      $repl = preg_replace( '/\s+file=".+"/', '', $matches[0][$tag_index] );      
      $post->post_content = str_replace( $matches[0][$tag_index], $repl, $content );
      wp_update_post( $post );
    }
  }  
}

function locationreport_archive_all_shortcodes( $fn, $path )
{ 
  $kml_str = file_get_contents( path_join( $path, $fn ) );
  $kml = KML::createFromText( $kml_str );    
  $doc = $kml->getFeature();
  $ftrs = $kml->getAllFeatures();
  
  foreach( $ftrs as $ftr )
  {
    if( is_a( $ftr, 'libKML\Placemark') )
    { 
      $geom = $ftr->getGeometry();
      if( is_a( $geom, 'libKML\Point') )
      {
        $id = $ftr->getId();
        locationreport_archive_shortcode( $id, $fn );          
      }  
    }      
  }
}

function locationreport_unarchive_all_shortcodes( $fn, $path )
{ 
  $kml_str = file_get_contents( path_join( $path, $fn ) );
  $kml = KML::createFromText( $kml_str );    
  $doc = $kml->getFeature();
  $ftrs = $kml->getAllFeatures();
  
  foreach( $ftrs as $ftr )
  {
    if( is_a( $ftr, 'libKML\Placemark') )
    { 
      $geom = $ftr->getGeometry();
      if( is_a( $geom, 'libKML\Point') )
      {
        $id = $ftr->getId();
        locationreport_unarchive_shortcode( $id );          
      }  
    }      
  }
}


function locationreport_options_page()
{
  if(isset($_POST['archive_route']))
  {
    $an = isset($_POST['archive_name'])?$_POST['archive_name']:'route_backup.kml';
    
    $upload_dir_arr = wp_upload_dir();
    $kmldir = path_join( $upload_dir_arr['basedir'], 'locationreport');
    $fn_route = path_join( $kmldir, 'route.kml');
    $fn_route_new = path_join( $kmldir, $an);
    $fn_latest = path_join( $kmldir, 'latest.kml');
    
    if(!copy( $fn_route, $fn_route_new))
      echo '<p>'.__('Error copying route.kml...', 'locationreport').'</p>';
    else 
    {  
      echo '<p>'.__('File route.kml archived as ', 'locationreport').$an.'</p>';
      
      locationreport_archive_all_shortcodes( $an, $kmldir );
    
      $el = isset($_POST['empty_or_last'])?$_POST['empty_or_last']:'last';
      switch ($el)
      {
      case "empty":
        $fn_empty = path_join( $kmldir, 'style.kml');
        if(!copy( $fn_empty, $fn_route))
          echo '<p>'.__('Error overwriting route.kml with style.kml...', 'locationreport').'</p>';
        elseif(!copy( $fn_empty, $fn_latest))
          echo '<p>'.__('Error overwriting latest.kml with style.kml...', 'locationreport').'</p>';
        else
          echo '<p>'.__('Started new empty route.', 'locationreport').'</p>';
        break;
      case "last":
        if(!copy( $fn_latest, $fn_route))
          echo '<p>'.__('Error overwriting route.kml with latest.kml...', 'locationreport').'</p>';
        else
        {
           echo '<p>'.__('Started new route with latest location.', 'locationreport').'</p>';
           
           locationreport_unarchive_all_shortcodes( 'route.kml', $kmldir );
        }  
        break;
      }
    }
  }
  
  include('locationreport-options.php'); 
}

add_action('admin_menu', 'locationreport_admin');
function locationreport_admin()
{
  add_options_page(__('LocationReport Manager', 'locationreport'), __('LocationReport', 'locationreport'), 'manage_options', basename(__FILE__), 'locationreport_options_page');
}

?>
