<?php
include_once("FDL/Lib.Util.php");
include_once("FDL/freedom_util.php");

function wgcal_viewevent(&$action) {

  $dbaccess = getParam("FREEDOM_DB");

  $id = GetHttpVars("id", 0);
  if ($id==0) {
    $action->lay->set("event", "Doc id not set");
    return;
  }

  $doc = new_Doc($dbaccess, $id);
  if (!$doc->isAffected()) {
    $action->lay->set("event", "Invalid docid");
    return;
  }
  $action->lay->set("event", $doc->viewdoc($doc->defaultview));
  return;
}

  

  
