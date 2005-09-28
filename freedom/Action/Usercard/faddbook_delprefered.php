<?php

function faddbook_delprefered(&$action) {

  $cid = GetHttpVars("cid", -1);
  if ($cid==-1) return;

  $cpref = $action->getParam("FADDBOOK_PREFERED", "");
  $tc = explode("|", $cpref);
  $ntc = array();
  foreach ($tc as $k => $v) if ($v!=$cid) $ntc[] = $v;
  $stc = implode("|", $ntc);
  $action->parent->param->set("FADDBOOK_PREFERED", $stc, PARAM_USER.$action->user->id, $action->parent->id);
  return;
}