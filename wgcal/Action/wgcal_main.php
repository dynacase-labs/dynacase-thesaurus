<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_main.php,v 1.6 2005/06/20 16:07:31 marc Exp $
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
  $action->lay->set("bordercolor", $theme->WTH_COLOR_2);
  $action->lay->set("toolbarwidth", $action->getParam("WGCAL_U_TOOLBARSZ", 250));
  $action->parent->param->set("WGCAL_U_CALCURDATE", time(), PARAM_USER.$action->user->id, $action->parent->id);

}
?>
