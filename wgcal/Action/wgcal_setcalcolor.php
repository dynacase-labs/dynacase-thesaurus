<?php
function wgcal_setcalcolor(&$action)
{

  $calid = GetHttpVars("calid", -1);
  if ($calid == -1)   return;

  $color = GetHttpVars("calcolor", "green");
  $new = true;
  $ncals = "";
  $cals = explode("|", $action->GetParam("WGCAL_U_RESSLISTED", ""));
  while (list($k,$v) = each($cals)) {
    $tc = explode("%", $v);
    if ($tc[0] == $calid) {
      $tc[2] = $color; 
      $new = false;
    }
    if ($tc[0]!="") $ncals .= $tc[0]."%".$tc[1]."%".$tc[2]."|"; 
  }
  if ($new) $ncals .= $calid."%0%".$color."|";
  $action->parent->param->set("WGCAL_U_RESSLISTED", $ncals, 
			      PARAM_USER.$action->user->id, $action->parent->id);
  redirect($action, $action->parent->name, "WGCAL_TOOLBAR");
}
?>
