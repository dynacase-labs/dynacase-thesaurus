<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("WGCAL/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_newcalendar(&$action) 
{
  $dbaccess = $action->getParam("FREEDOM_DB");
  $cname     = GetHttpVars("cname", "");
  $private   = GetHttpVars("cprivate", 1);
  if ($cname == "") return;
  $cal = createDoc($dbaccess, "SCALENDAR");
  $cal->Add();
  $cal->setValue("BA_TITLE", $cname);
  $err = $cal->Modify();
  if ($err!="") AddWarningMsg("$err");
  return;
}
?>