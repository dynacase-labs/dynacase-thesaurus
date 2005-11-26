<?php

include_once("WHAT/Lib.Common.php");
include_once("FDL/Class.Doc.php");
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.wTools.php");

function wgcal_todostore(&$action) {

  $db = $action->getParam("FREEDOM_DB");

  $id = GetHttpVars("idtodo", -1);
  $title = GetHttpVars("title", -1);
  $fdate = GetHttpVars("Dstart", 0);
  $date = substr(w_datets2db($fdate),0,11)." 00:00:00";
  $note =GetHttpVars("note");

  if ($id == -1) {
    $todo = createDoc($db, "TODO");
  } else {
    $todo = new_Doc($db, $id);
  }

  $todo->setValue("todo_idowner", $action->user->fid);
  $attru = GetTDoc($db, $ownerid);
  $ownertitle = $attru["title"];
  $todo->setValue("todo_owner", $ownertitle);
  $todo->setValue("todo_title", $title);
  $todo->setValue("todo_date", $date);
  $todo->setValue("todo_note", $note);
  
  $err = "";
  if (!$todo->IsAffected()) $err = $todo->Add();
  if ($err!="")  AddWarningMsg(__FILE__."::".__LINE__."$err");
  else {
    $err = $todo->Modify();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."$err");
    else {
      $err = $todo->PostModify();
      if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."$err");
    }
  }
  if ($todo->profid==0) {
    $todo->disableEditControl();
    $todo->SetProfil($todo->id);
    $todo->SetControl();
    $err = $todo->Modify();
    if ($err!="") AddWarningMsg(__FILE__."::".__LINE__."> $err");
    $todo->enableEditControl();
  }  
  redirect($action, "WGCAL", "WGCAL_TODOVIEW");
}


?>
