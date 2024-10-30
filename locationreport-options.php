<?php
/*
  Option page for locationreport wordpress plugin
*/

defined( 'ABSPATH' ) or die( 'Nope, not accessing this' );
?>

<div class="wrap">
<table border="0">
 <tr>
  <td><p><img src="<?php echo LOCATIONREPORT_PLUGIN_URL ?>icon-128x128.png" alt="Logo"></p></td>
  <td><h2>Location Report Plugin <?php echo LOCATIONREPORT_VER ?> </h2></td>
 </tr>
</table>

<h3><?php _e('Archive old route and start new one.', 'locationreport'); ?></h3>
<form method="post" id="locationreport-archive-form" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

  <p><?php _e( 'Filename for archived version of route.kml: ', 'locationreport' ); ?>
  <input id="archive_name" name="archive_name" type="text" size="30" value="route_<?php echo date('Y_m_d').'.kml'; ?>" />
  </p>
  
  <p><label><input type='radio' name='empty_or_last' value='last' checked='checked' /> <span class=""><?php _e('Start with last location.', 'locationreport'); ?></span></label><br />
	<label><input type='radio' name='empty_or_last' value='empty' /> <span class=""><?php _e('Start with empty route.', 'locationreport'); ?></label><br />
  </p>
  
  <input type="submit" name="archive_route" value="<?php _e('Archive old route and start new one.', 'locationreport'); ?>" class="button" />

</form>  

</div>

