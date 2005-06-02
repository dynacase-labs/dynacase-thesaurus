<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_main.php,v 1.3 2005/06/02 05:06:15 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_main(&$action) {
  $fsz = $action->getParam("WGCAL_U_FONTSZ", "normal");
  if (file_exists("WGCAL/Themes/$fsz.fsz")) include_once("WGCAL/Themes/$fsz.fsz");
  else include_once("WGCAL/Themes/default.fsz");
  $action->lay->set("toolbarwidth", $theme->WTH_TOOLBARW);
  echo "toolbarwidth = ".$theme->WTH_TOOLBARW."<br>";
}
?>
