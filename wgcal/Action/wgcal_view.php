<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_view.php,v 1.1 2005/07/05 04:54:23 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Lib.Util.php");
include_once("WGCAL/Lib.wTools.php");

define(WGCAL_VMODE_ONEWEEK, 0);
define(WGCAL_VMODE_TWOWEEK, 1);
define(WGCAL_VMODE_NDAYS, 2);
define(WGCAL_VMODE_MONTH, 3);

function wgcal_view(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get query
  $qid = getIdFromName($dbaccess, GetHttpVars("qid", 0));
  $ts_start = GetHttpVars("ts", 0);
  if ($ts_start!="") {
    if (!is_int($ts_start)) $ts_start = w_dbdate2ts($ts_start);
  } else {
    $jdate = GetHttpVars("jdate", 0);
    if ($jdate != 0) $ts_start = w_dbdate2ts(jd2cal($jdate));
    else $ts_start = time();
  }
  $vmode = GetHttpVars("vm", WGCAL_VMODE_ONEWEEK);
  switch ($vmode) {
  case WGCAL_VMODE_ONEWEEK: $ndays = 7; break;
  case WGCAL_VMODE_TWOWEEK: $ndays = 14; break;
  case WGCAL_VMODE_MONTH: $ndays = w_DaysInMonth($ts_start); break;
  default : $ndays = GetHttpVars("ndays", 7);
  }
  
  // Check for standalone mode 
  $sm = (GetHttpVars("sm", 0) == 0 ? false : true);

  $events = array();
  $qevents = new Doc($dbaccess, $qid);
  if (method_exists($qevents, "getEvents")) {
    
    $ds = w_datets2db(w_GetFirstDayOfWeek($ts_start));
    if ($vmode==WGCAL_VMODE_MONTH) {
      $month = strftime("%m", $ts_start);
      $year  = strftime("%Y", $ts_start);
      $ds = "".$year."-".$month."-01 00:00:00";
      $de = "".$year."-".$month."-".w_DaysInMonth($ts_start)." 23:59:59";
    } else {
      $de = $firstWeekDay + ($ndays * SEC_PER_DAY) - 1;
    }
    $events = $qevents->getEvents($ds, $de);
  }
}