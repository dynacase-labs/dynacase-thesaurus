<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_css.php,v 1.2 2005/02/17 07:11:09 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_css(&$action) {
  $themef = $action->getParam("WGCAL_U_THEME", "default");
  if (file_exists("WGCAL/wgcal_theme_".$themef.".php")) 
    include_once("WGCAL/wgcal_theme_".$themef.".php");
  else 
     include_once("WGCAL/wgcal_theme_default.php");
  $action->lay = new Layout("WGCAL/Layout/wgcal.css");
  foreach ($theme as $k => $v) $action->lay->set($k, $v);

  header("Cache-Control: private, max-age=3600");
  header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600)); 
  header("Pragma: ");
  header("Content-type: text/css");
}
?>
