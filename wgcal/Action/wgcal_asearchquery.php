<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_asearchquery.php,v 1.1 2005/02/01 15:12:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('WGCAL/WGCAL_external.php');

function wgcal_asearchquery(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");

  $action->parent->AddJsRef("FDL/Layout/jdate.js");

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_editevent.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  //int mktime ( [int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]] )

  $dstart = mktime(0,0,0,0,0,strftime("%Y",time()));
  $dend   = mktime(23,59,59,11,0,strftime("%Y",time()));
  
  $action->lay->set("dateformat", "%A %d %b %Y");

  $action->lay->set("dstart", $dstart);
  $action->lay->set("mdstart", $dstart*1000);
  $action->lay->set("rdstart", strftime("%A %d %b %Y", $dstart));

  $action->lay->set("dend", $dend);
  $action->lay->set("mdend", $dend*1000);
  $action->lay->set("rdend", strftime("%A %d %b %Y", $dend));

  $acal = CAL_getEventStates($action->GetParam("FREEDOM_DB"), "");
  foreach ($acal as $k => $v) {
    if ($status!=0 && $k==0) continue;
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $ic++;
  }
  $action->lay->SetBlockData("RVSTATUS", $tconf);
}
?>