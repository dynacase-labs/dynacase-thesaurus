<?php
function wgcal_setcalcolor(&$action)
{

  $calid = GetHttpVars("calid", -1);
  $color = GetHttpVars("calcolor", "green");

  if ($calid != -1)  {
    $new = true;
    $ncals = "";
    $cals = explode("|", $action->Read("WGCAL_RESSOURCES", ""));
    while (list($k,$v) = each($cals)) {
      $tc = explode("%", $v);
      if ($tc[0] == $calid) {
	$tc[2] = $color; 
	$new = false;
      }
      if ($tc[0]!="") $ncals .= $tc[0]."%".$tc[1]."%".$tc[2]."|"; 
    }
    if ($new) $ncals .= $calid."%0%".$color."|";
    $action->Register("WGCAL_RESSOURCES", $ncals); 
  }
  redirect($action, $action->parent->name, "WGCAL_TOOLBAR");
}
?>
