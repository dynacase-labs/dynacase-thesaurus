<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_main.php,v 1.11 2005/12/09 11:21:28 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("WGCAL/Lib.wTools.php");
include_once("WGCAL/Lib.Agenda.php");

function wgcal_main(&$action) {

  $cal = MonAgenda();

  $fsz = $action->getParam("WGCAL_U_FONTSZ", "normal");
  if (file_exists("WGCAL/Themes/$fsz.fsz")) include_once("WGCAL/Themes/$fsz.fsz");
  else include_once("WGCAL/Themes/default.fsz");
  $action->lay->set("bordercolor", $theme->WTH_COLOR_2);
  $action->lay->set("toolbarwidth", $action->getParam("WGCAL_U_TOOLBARSZ", 250));
  $action->lay->set("viewTodo", ($action->getParam("WGCAL_U_TBTODOS") == 1 ? true : false));
  $action->parent->param->set("WGCAL_U_CALCURDATE", time(), PARAM_USER.$action->user->id, $action->parent->id);

}
?>
