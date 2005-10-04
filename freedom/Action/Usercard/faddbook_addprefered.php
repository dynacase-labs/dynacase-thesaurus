<?php

function faddbook_addprefered(&$action) {

  $cid = GetHttpVars("cid", -1);
  if ($cid==-1) return;
  $cpref = $action->getParam("FADDBOOK_PREFERED", "");
  $tc = explode("|", $cpref);
  $found = false;
  foreach ($tc as $k => $v) if ($v==$cid) $found = true;
  if (!$found) {
    $tc[] = $cid;
    $stc = implode("|", $tc);
    $action->parent->param->set("FADDBOOK_PREFERED", $stc, PARAM_USER.$action->user->id, $action->parent->id);
  }
  Redirect($action, "USERCARD", "FADDBOOK_PREFERED");
}
?>
