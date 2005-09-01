<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_main.php,v 1.8 2005/09/01 16:48:27 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once("WGCAL/Lib.wTools.php");
include_once("WGCAL/Class.UCalVis.php");

function wgcal_main(&$action) {

//   MonAgenda();

  $ucalv = new UCalVis($action->getParam("FREEDOM_DB"));
  if (!$ucalv->isUCalInit($action->user->fid, 0)) {
  $tgr = wSpeedWFidGroups();
    $ucalv->ucalvis_ucal = 0;
    $ucalv->ucalvis_ufid = $action->user->fid; 
    $ucalv->ucalvis_uwid = $action->user->id;  
    $ucalv->ucalvis_gfid = $tgr["byWid"][2]; 
    $ucalv->ucalvis_gwid = 2; 
    $ucalv->ucalvis_mode = 1;
    $ucalv->Add();
  }    


  $fsz = $action->getParam("WGCAL_U_FONTSZ", "normal");
  if (file_exists("WGCAL/Themes/$fsz.fsz")) include_once("WGCAL/Themes/$fsz.fsz");
  else include_once("WGCAL/Themes/default.fsz");
  $action->lay->set("bordercolor", $theme->WTH_COLOR_2);
  $action->lay->set("toolbarwidth", $action->getParam("WGCAL_U_TOOLBARSZ", 250));
  $action->parent->param->set("WGCAL_U_CALCURDATE", time(), PARAM_USER.$action->user->id, $action->parent->id);
}
?>
