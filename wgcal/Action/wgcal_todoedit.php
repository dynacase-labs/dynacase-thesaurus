<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once('WGCAL/Lib.WGCal.php');

function wgcal_todoedit(&$action) {

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");

  $db = $action->getParam("FREEDOM_DB");

  $title = "";
  $date  = date2db(time() + ($action->getParam("WGCAL_U_TODODEFLIMIT", 7) * (24*3600)));
  $note = "";

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
  
  $action->lay->set("todoId", $id);
  $action->lay->set("todoTitle", $title);
  $action->lay->set("todoNote", $note);
  
  $action->lay->set("todoDateV", dbdate2ts($date));
  $action->lay->set("todoDateMs", (dbdate2ts($date)*1000));
  $action->lay->set("todoDateT", substr($date,0,11));
}


?>
