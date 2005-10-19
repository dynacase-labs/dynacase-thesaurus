<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Api.WGCal.php,v 1.1 2005/10/19 05:40:32 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('WGCAL/Lib.wTools.php');


 /**
 */
function WgcalGetWaitingRendezVous() {
  global $action;
  $rv = array();
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $today = ts2db(time(), "Y-m-d 00:00:0");
  $filter[] = "(calev_start > '".$today."' ) AND (calev_attid ~* '".$action->user->fid."')";
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, 
		      $action->user->id, "TABLE", getIdFromName($dbaccess,"CALEVENT"));
  if (count($rdoc)>0) {
    foreach ($rdoc as $k => $v) {
      $doc = getTDoc($action->GetParam("FREEDOM_DB"), $v["id"]);
      $attid = Doc::_val2array($doc["calev_attid"]);
      $attst = Doc::_val2array($doc["calev_attstate"]);
      $state = -1;
      foreach ($attid as $ka => $va) {
	if ($va==$action->user->fid && 
	    (   $attst[$ka]==EVST_NEW 
		|| $attst[$ka]==EVST_READ
		||$attst[$ka]==EVST_TBC ) ) $state = $attst[$ka]; 
      }
      if ($state!=-1) $rv[] = $rdoc[$k];
    }
  }
  return $rv;
}

 /**
 */
function WgcalGetMyNextRendezVous() {
  global $action;
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fti[] =  getIdFromName($dbaccess, "CALEVENT");

  setHttpVar("idfamref", implode("|", $fti));
  setHttpVar("ress", $action->user->fid);

  $delta = 14 * 24 * 3600; // two weeks
  $start = ts2db(time(), "Y-m-d 00:00:0");
  $end = ts2db((time()+$delta), "Y-m-d 23:59:59");

  $edre = wGetEvents($start, $end, true); 
  $rvbydate = array();
  if (count($edre) > 0) {
    foreach ($edre as $k => $v) {
      $crv  = getTDoc($dbaccess, $v["IDP"]);

      $d = substr($v["TSSTART"],0,2);
      $m = substr($v["TSSTART"],3,2);
      $y = substr($v["TSSTART"],6,4);      
      $day = mktime(0,0,0,$m,$d,$y);
      if (!isset($rvbydate[$day]["cnt"])) {
	$rvbydate[$day]["cnt"] = 0;
	$rvbydate[$day]["date"] = $day;
	$rvbydate[$day]["event"] = array();
      }
      $i = $rvbydate[$day]["cnt"];
      $rvbydate[$day]["event"][$i] = $crv;
      $rvbydate[$day]["cnt"]++;
    }
    if (count($rvbydate)>0) {
      uasort($rvbydate, "WgcalApiDaySort");
       foreach ($rvbydate as $k => $v) { 
	 uasort($rvbydate[$k]["event"], "WgcalApiRvSort");
       }
    }
  }
  return $rvbydate;
}

function WgcalApiDaySort($a, $b) {
  return $a["date"] > $b["date"] ;
}
function WgcalApiRvSort($a, $b) {
  return strcmp($a["start"], $b["start"]);
}

?>