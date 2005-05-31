<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_tododone(&$action) {

  $db = $action->getParam("FREEDOM_DB");
  $id = GetHttpVars("idtodo", -1);
  if ($id!=-1) {
    $todo = new Doc($db, $id);
    if ($todo->isAlive()) $todo->delete();
  }
  redirect($action, "WGCAL", "WGCAL_TOOLBAR");
}


?>