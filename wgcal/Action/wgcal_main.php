<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_main.php,v 1.2 2005/03/08 22:40:03 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

function wgcal_main(&$action) {
  $themef = $action->getParam("WGCAL_U_THEME", "default");
  if (file_exists("WGCAL/Themes/$themef.thm"))
    include_once("WGCAL/Themes/$themef.thm");
  else
    include_once("WGCAL/Themes/default.thm");
  $action->lay->set("toolbarwidth", $theme->WTH_TOOLBARW);
}
?>
