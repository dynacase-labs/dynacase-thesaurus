<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_htmlhead.php,v 1.4 2005/03/30 10:04:41 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: wgcal_htmlhead.php,v 1.4 2005/03/30 10:04:41 marc Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function wgcal_htmlhead(&$action) {


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
