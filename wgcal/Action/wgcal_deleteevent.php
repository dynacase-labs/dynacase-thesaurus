<?php

include_once("FDL/Class.Doc.php");

function wgcal_deleteevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  
  $id  = GetHttpVars("eventid", -1);
  if ($id!=-1) {
    $event = new Doc($db, $id);
    if ($event->isAlive()) {
      $err = $event->Delete();
      $err = $event->postDelete();
    }
  }
  redirect($action, $action->parent->name, "WGCAL_CALENDAR");
}
?>