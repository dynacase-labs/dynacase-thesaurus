<?php
function wgcal_setressdisplay(&$action)
{

  $calid = GetHttpVars("calid", -1);
  $state = GetHttpVars("calact", 0);

  $new = true;
  $ncals = "";
  $cals = explode("|", $action->GetParam("WGCAL_U_RESSLISTED", ""));
  while (list($k,$v) = each($cals)) {
    $tc = explode("%", $v);
   if ($tc[0] == $calid) {
      $tc[1] = $state; 
      $new = false;
    }
    if ($tc[0]!="") $ncals .= $tc[0]."%".$tc[1]."%".$tc[2]."|"; 
  }
  $action->parent->param->set("WGCAL_U_RESSLISTED", $ncals, 
			      PARAM_USER.$action->user->id, $action->parent->id);
  redirect($action, $action->parent->name, "WGCAL_CALENDAR");
}
?>
