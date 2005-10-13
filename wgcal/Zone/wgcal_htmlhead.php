<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_htmlhead.php,v 1.11 2005/10/13 14:11:54 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: wgcal_htmlhead.php,v 1.11 2005/10/13 14:11:54 marc Exp $


include_once('Class.QueryDb.php');
include_once('Class.Application.php');
include_once('WGCAL/Lib.wTools.php');

function wgcal_htmlhead(&$action) {

  global $_SERVER;

  // Themes ----------------------------------------------------------
  $themes = array();
  $themedir = "WGCAL/Themes";
  $ith=0;
  $list = GetFilesByExt($themedir, ".thm");
  $flist = GetFilesByExt($themedir, ".fsz");
  foreach ($list as $k => $v) {
    foreach ($flist as $kf => $vf) {
      $themes[] = array( "theme" => $v, 
			 "font" =>  $vf,
			 "title" => "$v ($vf fonts)");
    }
  }
//   $action->lay->setBlockData("OTHEMES", $themes);
   $action->lay->setBlockData("OTHEMES", null);


  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $refresh = GetHttpVars("refresh", 0);
  if ($refresh>0) {
    $action->lay->set("refresh", true);
    $action->lay->set("refresh_time", $refresh);
    $url = ($_SERVER["SERVER_PORT"] == 443 ? "https" : "http") ."://"
      .    $_SERVER["SERVER_NAME"]
      .    ":".$_SERVER["SERVER_PORT"]
      .    $_SERVER["REQUEST_URI"];
    $action->lay->set("refresh_url", $url);
  } else {
    $action->lay->set("refresh", false);
  }

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
  
  $unload_hand = GetHttpVars("HUL", "");
  $berforeunload_hand = GetHttpVars("HBUL", "");
  $action->lay->set("onUnLoad", $unload_hand);
  $action->lay->set("onbeforeunload", $berforeunload_hand);
  
}

?>
