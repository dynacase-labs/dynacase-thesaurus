<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.3 2005/06/03 05:15:05 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_gview(&$action) {
  global $order;

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $ds      = GetHttpVars("ds","");
  $de      = GetHttpVars("de","");
  $order   = GetHttpVars("order", "C"); // C chocolatine D Decroissant
  $filter  = GetHttpVars("filter" , ""); // "att=val[|att=val]"
  $menu    = GetHttpVars("menu", 1); 
  $famids  = GetHttpVars("famids", ""); 
  $ressids = GetHttpVars("ressids", $action->user->fid); 
  $explode = (GetHttpVars("explode", "") == 1 || GetHttpVars("explode", "") == true ? true : false);
  $debug   = GetHttpVars("debug", false);

  if ($debug) echo "GVIEW:";
  // Set producer families
  if ($famids=="") {
    $famids = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  }
  $ft = explode("|", $famids);
  $fti = array();
  foreach ($ft as $k => $v) $fti[] = getIdFromName($dbaccess, $v);
  $idfamref = implode("|", $fti);
  if ($debug) echo " idfamref=[$idfamref]";
  setHttpVar("idfamref", $idfamref);

  // Set event ressources
  if ($ressids == "") {
    $viewme=false;
    $ress = WGCalGetRessDisplayed($action);
    $tr=array(); 
    $ire=0;
    foreach ($ress as $kr=>$vr) {
      if ($vr->id>0) $tr[$ire++] = $vr->id;
      if ($vr->id==$action->user->fid) $viewme=true;
    }
    if ($viewme) {
      $grp = WGCalGetRGroups($action, $action->user->id);
      foreach ($grp as $kr=>$vr) $tr[$ire++] = $vr;
    }
    $ressids = implode("|", $tr);
  }
  if ($debug) echo " idres=[$ressids]";
  setHttpVar("idres", $ressids);

  // Set a filter
  $evfilter = array();
  if ($filter!="") $evfilter = explode("|",$filter);
  if ($debug) echo " filter=[$filter]";
  print_r2($evfilter);
  
  $reid=getIdFromName($dbaccess,"WG_AGENDA", $explode, $evfilter);
  $dre = new Doc($dbaccess,$reid);
  $edre = array();
  $edre = $dre->getEvents($ds,$de);
  foreach ($edre as $k => $v) {

    $day = substr($v["evt_begdate"],0,10);

    if (!isset($btime[$day]["cnt"])) {
      $btime[$day]["date"] = $day;
      $btime[$day]["cnt"] = 0;
      $devents[$day] = array();
    }
    $i = $btime[$day]["cnt"];


    $j = count($devents[$day]);
    $hs = substr($v["evt_begdate"],11,5);
    $he = substr($v["evfc_realenddate"],11,5);
    $devents[$day][$j]["id"] = $v["id"];
    $devents[$day][$j]["fid"] = $v["evfc_idinitiator"];
    $devents[$day][$j]["date"] = $day;
    $devents[$day][$j]["start"] = $hs;
    $devents[$day][$j]["end"] = $he;
    $devents[$day][$j]["title"] = $v["title"];

    $btime[$day]["cnt"]++;
  }

  uasort($btime, "daySort");
  $action->lay->setBlockData("btime", $btime);
  foreach ($devents as $k => $v) { 
    uasort($devents[$k], "evSort");
    $action->lay->setBlockData("devents$k", $devents[$k]);
  }
  
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
//   echo " a=".$a["date"]." ($ad)  b=".$b["date"]." ($bd)   ==> $r <br>";
  return $r;
}
function evSort($a, $b) {
  return strcmp($a["start"], $b["start"]);
}
?>