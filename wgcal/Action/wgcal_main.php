<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_main.php,v 1.1 2005/02/18 15:39:38 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_main(&$action) {
  $themef = $action->getParam("WGCAL_U_THEME", "default");
  if (file_exists("WGCAL/wgcal_theme_".$themef.".php")) 
    include_once("WGCAL/wgcal_theme_".$themef.".php");
  else 
     include_once("WGCAL/wgcal_theme_default.php");
  $action->lay->set("toolbarwidth", $theme["WTH_TOOLBARW"]);
}
?>
