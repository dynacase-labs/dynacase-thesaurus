<?php

include_once("FDL/Class.Doc.php");

function wgcal_deleteevent(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  
  $id  = GetHttpVars("eventid", -1);
  if ($id!=-1) {
    $event = new Doc($db, $id);
    $err = "xxx";
    if ($event->isAlive()) $err = $event->Delete();
    print_r2($err);
  }
}
?>