<?php

function faddbook_delprefered(&$action) {

  $cid = GetHttpVars("cid", 0);
  if ($cid<0 && $cid!=-2) return;
  if ($cid==-2) {
    $stc = "";
  } else {
    $cpref = $action->getParam("FADDBOOK_PREFERED", "");
    $tc = explode("|", $cpref);
    $ntc = array();
    foreach ($tc as $k => $v) if ($v!=$cid) $ntc[] = $v;
    $stc = implode("|", $ntc);
  }
  $action->parent->param->set("FADDBOOK_PREFERED", $stc, PARAM_USER.$action->user->id, $action->parent->id);
  Redirect($action, "USERCARD", "FADDBOOK_PREFERED");
}