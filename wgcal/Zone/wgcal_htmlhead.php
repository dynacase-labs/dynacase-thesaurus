<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_htmlhead.php,v 1.6 2005/06/18 04:30:47 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: wgcal_htmlhead.php,v 1.6 2005/06/18 04:30:47 marc Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function wgcal_htmlhead(&$action) {


  $reload = GetHttpVars("reload", 0);

  if ($reload>0) {
    $action->lay->set("reload", true);
    $action->lay->set("reloadtime", $reload);
  } else $action->lay->set("reload", false);

  $sTitle = GetHttpVars("S", "");
  $winW = GetHttpVars("W", 0);
  $winH = GetHttpVars("H", 0);
  if ($winW>0 && $winH>0) {
    $action->lay->set("resize", true);
    $action->lay->set("winW", $winW);
    $action->lay->set("winH", $winH);
  } else {
    $action->lay->set("resize", false);
  } 

  $rs = strtoupper(GetHttpVars("RZ", ""));
  $Hresize = $Vresize = false;
  if (strpos($rs,"H")!==false) $Hresize=true; 
  if (strpos($rs,"V")!==false) $Vresize=true; 
  $action->lay->set("Hresize", $Hresize);
  $action->lay->set("Vresize", $Vresize);
    
  $action->lay->set("SubTitle", $sTitle);
  $action->lay->set("bstitle", ($sTitle!="" ? true : false));
  
  $theme = $action->getParam("WGCAL_U_THEME", "default");
  $action->lay->set("theme", $theme);
  
  if (GetHttpVars("f",0)==1) {
    $r = $action->getParam("WGCAL_U_REFRESH_T", 0);
    if ($r==1) {
      $t[0]["refreshurl"] = "[CORE_STANDURL]&app=WGCAL&action=WGCAL_TOOLBAR&f=1";
      $t[0]["refreshdur"] = "10";
    }
    $action->lay->setBlockData("autorefresh", $t);
  } 
    
}
?>
