<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_getjsevent.php,v 1.1 2006/07/07 15:32:10 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.wTools.php");
include_once('FDL/popup_util.php');
include_once('WHAT/Lib.Common.php');

function wgcal_getjsevent(&$action) {


  // Search event

  $showWeekEnd = ($action->GetParam("WGCAL_U_VIEWWEEKEND", "yes")=="yes" ? true : false);
  $startDdate = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $dayperweek = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  $firstWeekDay = w_GetFirstDayOfWeek($startDdate);
  $edate = $firstWeekDay + ($dayperweek * SEC_PER_DAY) - 1;
  $d1 = ts2db($firstWeekDay, "Y-m-d 00:00:00");
  $d2 = ts2db($edate, "Y-m-d 23:59:59");
  $filter = array();
  $events = wGetEvents($d1, $d2, true, $filter, "EVENT_FROM_CAL");

  $action->lay->SetBlockData("Events", $events);
  $action->lay->set("status", 1);
  $action->lay->set("count", count($events));
  $action->lay->set("statustext", "date=[$d1,$d2]");

  return;
  

}
?>