<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_css.php,v 1.5 2005/05/25 15:28:28 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_css(&$action) {
  $css = GetHttpVars("css", "wgcal");
  $action->lay = new Layout("WGCAL/Layout/".$css.".css");
  $themef = GetHttpVars("theme", $action->getParam("WGCAL_U_THEME", "default"));
  
  @include_once("WGCAL/Themes/default.thm");
  @include_once("WGCAL/Themes/".$themef.".thm");
  
  $action->lay->set("THEME", $themef);
  $vars = get_object_vars($theme);
  foreach ($vars as $k => $v) $action->lay->set($k, $v);

  header("Cache-Control: private, max-age=3600");
  header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600)); 
  header("Pragma: ");
  header("Content-type: text/css");
}
?>
