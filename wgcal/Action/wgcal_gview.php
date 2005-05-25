<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.2 2005/05/25 15:28:28 marc Exp $
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

  $ds     = GetHttpVars("ts","");
  $de     = GetHttpVars("te","");
  $rlist  = GetHttpVars("r", $action->parent->user->fid);
  setHttpVar("idres", $rlist);
  $order  = GetHttpVars("o", "C"); // C chocolatine D Decroissant
  $menu   = GetHttpVars("m", 1); 
  
  $fref = $action->getParam("WGCAL_G_VFAM", "CALEVENT");
  $ft = explode("|", $fref);
  $fti = array();
  foreach ($ft as $k => $v) $fti[] = getIdFromName($dbaccess, $v);
  $idfamref = implode("|", $fti);
  setHttpVar("idfamref", $idfamref);

  $reid=getIdFromName($dbaccess,"WG_AGENDA");
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