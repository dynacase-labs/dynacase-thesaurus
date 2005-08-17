<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.12 2005/08/17 16:58:02 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once('FDL/popup_util.php');
include_once('WHAT/Lib.Common.php');

function wgcal_gview(&$action) {
  global $order;

  $themef = GetHttpVars("theme", $action->getParam("WGCAL_U_THEME", "default"));
  @include_once("WGCAL/Themes/default.thm");
  @include_once("WGCAL/Themes/".$themef.".thm");

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("bcolor", $theme->WTH_COLOR_2);

  $ds      = GetHttpVars("ds","");
  $de      = GetHttpVars("de","");
  $order   = GetHttpVars("order", "D"); // C chocolatine D Decroissant
  $title   = GetHttpVars("title" , ""); 
  $filteron  = GetHttpVars("filteron" , ""); 
  $menu    = GetHttpVars("menu", 1); 
  $famids  = GetHttpVars("famids", ""); 
  $ressids = GetHttpVars("rlist", $action->user->fid); 
  $explode = ((GetHttpVars("explode", "") == 1)? true : false);
  $standalone = (GetHttpVars("standalone", "Y")=="Y" ? true : false);
  

  // Set producer families
  if ($famids=="") {
    $famids = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  }
  $ft = explode("|", $famids);
  $fti = array();
  foreach ($ft as $k => $v) $fti[] = getIdFromName($dbaccess, $v);
  $idfamref = implode("|", $fti);
  setHttpVar("idfamref", $idfamref);

  // Set event ressources
  setHttpVar("idres", $ressids);

  // Set a filter
  $evfilter = array();
  $ff = "";
  if ($title!="") {
    if ($filteron == "" || $filteron == "title") $ff = "(title ~* '".$title."')";
    if ($filteron == "" || $filteron == "desc") {
      $ff .= ($ff == "" ? "" : " or ");
      $ff .= "(evt_desc ~* '".$title."')";
    }
    $evfilter[] = $ff;
  }
  $reid=getIdFromName($dbaccess,"WG_AGENDA");
  $dre = new Doc($dbaccess,$reid);
  $edre = array();
  $edre = wGetEvents($ds, $de);
  foreach ($edre as $k => $v) {

    $d = substr($v["TSSTART"],0,2);
    $m = substr($v["TSSTART"],3,2);
    $y = substr($v["TSSTART"],6,4);

    $day = strftime("%d %B %Y",mktime(0,0,0,$m,$d,$y));
  
    if (!isset($btime[$day]["cnt"])) {
      $btime[$day]["date"] = $day;
      $btime[$day]["cnt"] = 0;
      $devents[$day] = array();
    }
    $i = $btime[$day]["cnt"];
    $j = count($devents[$day]);
    if ($action->GetParam("WGCAL_U_PORTALSTYLE", "TABLE")== "FIELDSET") $devents[$day][$j]["EvCard"] = $v["EvRCard"];
    else $devents[$day][$j]["EvCard"] = $v["EvSTCard"];
    $devents[$day][$j]["IDP"] = $v["IDP"];
    $devents[$day][$j]["TSSTART"] = $v["TSSTART"];
    $devents[$day][$j]["RG"] = $v["RG"];
  }

  if (count($btime)>0) {
    uasort($btime, "daySort");
    $action->lay->setBlockData("btime", $btime);
    if (count($devents)>0) {
      foreach ($devents as $k => $v) { 
	uasort($devents[$k], "evSort");
	$action->lay->setBlockData("devents$k", $devents[$k]);
      }
      $action->lay->set("noresult", false);
    } else {
      $action->lay->set("noresult", true);
    }
  } else {
    $action->lay->set("noresult", true);
  }
  $action->lay->set("standalone", $standalone);
  $action->lay->set("title", $title);

}

function daySort($a, $b) {
  global $order;
  if ($order!="C") {
    $t = $a;
    $a = $b;
    $b = $t;
  }
  $ad = substr($a["date"],6,4) . substr($a["date"],3,2) . substr($a["date"],0,2);
  $bd = substr($b["date"],6,4) . substr($b["date"],3,2) . substr($b["date"],0,2);
  if ($ad == $bd) $r = 0;
  else $r = (($ad > $bd) ? -1 : 1);
  return $r;
}
function evSort($a, $b) {
  return strcmp($a["start"], $b["start"]);
}
?>
