<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_editevent.php,v 1.4 2004/12/17 15:46:25 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */

include_once("FDL/Class.Doc.php");

function wgcal_editevent(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_editevent.js");
  
  $cssfile = $action->GetLayoutFile("calendar-default.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  

  $time = GetHttpVars("time", time());
  InitNewRv($action, $time);

}


function InitNewRv(&$action, $time) {

  $action->lay->set("TITLE", "");
  $action->lay->set("DESCR", "");

  $action->lay->set("ALLDAY", 0);
  $action->lay->set("NOHOUR", 0);

  $action->lay->set("ALARM", 0);
  $inc = 5;
  for ($min=0; $min<60; $min+=$inc) {
    $r = ($min==0?0:($min/$inc));
    $m[$r]["ALRMPERIOD_V"] = $min;
    if ($min==0)  $m[$r]["ALRMPERIOD_S"] = "selected";
    else  $m[$r]["ALRMPERIOD_S"] = "";
  } 
  $action->lay->SetBlockData("ALARM_MIN", $m);
  for ($hour=0; $hour<24; $hour++) {
    $h[$hour]["ALRMPERIOD_V"] = $hour;
    if ($hour==0)  $h[$hour]["ALRMPERIOD_S"] = "selected";
    else  $h[$hour]["ALRMPERIOD_S"] = "";
  } 
  $action->lay->SetBlockData("ALARM_HR", $h);
  
  $fdate = "%d/%m/%Y";
  $now = $time;
  $action->lay->set("START", $now);
  $action->lay->set("mSTART", $now*1000);
  $action->lay->set("STARTREAD", strftime($fdate, $now));
  $action->lay->set("H_START", strftime("%H", $now));
  $action->lay->set("M_START", "00");

  $end = $now + 3600;
  $action->lay->set("END", $end);
  $action->lay->set("mEND", $end*1000);
  $action->lay->set("ENDREAD", strftime($fdate, $end));
  $action->lay->set("H_END", strftime("%H", $end));
  $action->lay->set("M_END", "00");



  // Repeat zone
  $action->lay->set("REPEAT_SELECTED", "");

  for ($i=0; $i<=3; $i++)  $action->lay->set("REPEATTYPE_".$i, "");
  $action->lay->set("REPEATTYPE_0", "checked");

  $action->lay->set("D_RWEEKDISPLAY", "none");
  for ($i=1; $i<=7; $i++)  $action->lay->set("D_RWEEKDISPLAY_".$i, "");
  $action->lay->set("D_RWEEKDISPLAY_1", "checked");
  $action->lay->set("D_RMONTH", "none");
  $action->lay->set("D_RMONTH_DATE_CHECKED", "checked");
  $action->lay->set("D_RMONTH_DAY_CHECKED", "");

  $action->lay->set("D_RUNTIL_0", "checked");
  $action->lay->set("D_RUNTIL_1", "");
  $action->lay->set("RUNUNTIL_DATE_DISPLAY", "none");
  
  $action->lay->set("uDate", strftime("%d/%m/%y", time()));
  $action->lay->set("umDate", time()*1000);
  
  $action->lay->set("FREQVALUE", 1);


  // Excluded dates
  $action->lay->setBlockData("EXCLDATE", null);
  

}    

?>