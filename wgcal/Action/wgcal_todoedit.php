<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once('WGCAL/Lib.wTools.php');
include_once('WGCAL/Lib.WGCal.php');

function wgcal_todoedit(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $db = $action->getParam("FREEDOM_DB");

  $title = "";
  $date  = w_ts2dbdate(time() + ($action->getParam("WGCAL_U_TODODEFLIMIT", 7) * (24*3600)));
  $note = "";

  $action->lay->set("target", GetHttpVars("target", "_self"));
  $action->lay->set("act", GetHttpVars("act", "WGCAL_ALLTODO"));

  $id = GetHttpVars("idtodo", -1);
  if ($id!=-1) {
    $todo = new Doc($db, $id);
    if ($todo->isAlive()) {
      $id = $todo->id;
      $title = $todo->getValue("todo_title");
      $date = $todo->getValue("todo_date");
      $note = $todo->getValue("todo_note");
    } else {
      $id = -1;
    }
  }
  
  $action->lay->set("action", GetHttpVars("act", ""));
  $action->lay->set("todoId", $id);
  $action->lay->set("todoTitle", $title);
  $action->lay->set("todoNote", $note);
  
  $dbd = w_dbdate2ts($date);
  $action->lay->set("todoDateV",  $dbd);
  $action->lay->set("todoDateMs",($dbd*1000));
  $action->lay->set("todoDateT", w_strftime($dbd,WD_FMT_DAYFTEXT));
}


?>
