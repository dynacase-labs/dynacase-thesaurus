<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_editevent.php,v 1.1 2004/12/09 17:30:57 marc Exp $
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

 $cssfile = $action->GetLayoutFile("calendar-default.css");
 $csslay = new Layout($cssfile,$action);
 $action->parent->AddCssCode($csslay->gen());

 InitNewRv($action);

}


function InitNewRv(&$action) {

  $action->lay->set("TITLE", "");
  $action->lay->set("DESCR", "");

  $action->lay->set("ALLDAY", 0);
  $action->lay->set("NOHOUR", 0);

  $action->lay->set("ALARM", 0);
  $inc = 5;
  for ($min=0; $min<60; $min+$inc) {
    $min[($min==0?0:$min/$inc)]["ALRMPERIOD_V"] = sprintf("%2.2d", $min);
    $min[($min==0?0:$min/$inc)]["ALRMPERIOD_S"] = "";
 }
  $action->lay->SetBlockData("ALARM_MIN", $min);
  
  $now = time();
  $action->lay->set("START", $now);
  $action->lay->set("mSTART", $now*1000);
  $action->lay->set("STARTREAD", strftime("%d %b %Y %H:%M",$now));

  $end = $now + 3600;
  $action->lay->set("END", $end);
  $action->lay->set("mEND", $end*1000);
  $action->lay->set("ENDREAD", strftime("%d %b %Y %H:%M",$end));
}
?>