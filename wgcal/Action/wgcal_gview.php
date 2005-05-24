<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_gview.php,v 1.1 2005/05/24 05:28:46 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_gview(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $ds     = GetHttpVars("ts","2005-01-01 00:00:00");
  $de     = GetHttpVars("te","3005-12-31 23:59:59");
  $rlist  = GetHttpVars("rl", $action->parent->user->fid);
  setHttpVar("idres", $rlist);
  $order  = GetHttpVars("or", "C"); // C chocolatine D Decroissant
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
      $btime[$day]["devents"] = array();
    }
    $i = $btime[$day]["cnt"];

    $btime[$day]["devents"][$i]["id"] = $v["id"];
    $hs = substr($v["evt_begdate"],11,5);
    $he = substr($v["evfc_realenddate"],11,5);

    $btime[$day]["devents"][$i]["fid"] = $v["evfc_idinitiator"];
    $btime[$day]["devents"][$i]["start"] = $hs;
    $btime[$day]["devents"][$i]["end"] = $he;
    $btime[$day]["devents"][$i]["title"] = $v["title"];

    $btime[$day]["cnt"]++;
  }
  print_r2($btime);
  $action->lay->setBlockData("btime", $btime);


}
?>