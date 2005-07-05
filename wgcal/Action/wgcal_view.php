<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_view.php,v 1.2 2005/07/05 10:04:59 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Util.php");
include_once("WGCAL/Lib.wTools.php");

function wgcal_view(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get query
  $sqid = GetHttpVars("qid", 0);
  $qid = getIdFromName($dbaccess, $sqid);
  addTrace("Search Id (qid) = [$sqid($qid)]");

  $ts_start = GetHttpVars("ts", "");
  if ($ts_start!="") {
    if (!is_numeric($ts_start)) {
      $ts_start = w_dbdate2ts($ts_start);
    }
  }   

  $ts_end = GetHttpVars("te", "");
  if ($ts_end!="") {
    if (!is_numeric($ts_end)) {
      $ts_end = w_dbdate2ts($ts_end);
    }
  }

  addTrace("Start date (ts|js) = [$ts_start]");
  addTrace("End date (ts|js) = [$ts_end]");

  $vwe = GetHttpVars("vwe", 1);
  addTrace("View week-end (vwe) = [$vwe]");
  
  // Check for standalone mode 
  $sm = (GetHttpVars("sm", 0) == 0 ? false : true);

  $events = array();
  $qevents = new Doc($dbaccess, $qid);
  if (method_exists($qevents, "getEvents")) {
    $ds = ($ts_start!="" ? w_strftime($ts_start, WD_FMT_QSEARCH) : "");
    $de = ($ts_end!="" ? w_strftime($ts_end, WD_FMT_QSEARCH) : "");
    addTrace("Query getEvents('$ds', '$de')");
    $events = $qevents->getEvents($ds, $de);
    foreach ($events as $kv => $vv) {
      addTrace("event[".$vv["evt_frominitiatorid"]."::".$vv["id"]."] [".$vv["title"]."] s=".$vv["evt_begdate"]." e=".$vv["evt_enddate"]);
    }
  }
  showTrace();

  displayEvents($events);
}

function displayEvents(&$events, $lay = "CALENDAR") 
{
  global $action;
  $action->lay = new Layout($action->GetLayoutFile("wgcal_zcalendar.xml"),$action); 
}

?>

