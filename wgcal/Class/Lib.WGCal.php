<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Lib.WGCal.php,v 1.2 2004/12/01 17:07:08 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


// ---------------------------------------------------------------------------------------
// Left bar tools
// ---------------------------------------------------------------------------------------

define(CAL_T_NONE, -1);
define(CAL_T_NAVIGATOR, 0);
define(CAL_T_CALSELECTOR, 1);
define(CAL_T_TODAY, 2);

function WGCalToolSwitchState(&$action, $tool) {
  $val = "";
  $fnd = false;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  if (count($all)>0) {
    while (list($k, $v) = each($all)) {
      $t = explode("%",$v);
      if ($tool == $t[0]) {
	$ns = ($t[1] == 0 ? 1 : 0 );
	$fnd = true;
      }
      else 
	$ns = $t[1];
      $nv = $t[0]."%".$ns;
      if ($val!="") $val .= "|";
      $val .= $nv;
    }
  } 
  if (!$fnd) {
    if ($val!="") $val .= "|";
    $val = $tool."%1";
  }
  $action->parent->param->set("WGCAL_U_TOOLSSTATE", $val, PARAM_USER.$action->user->id, $action->parent->id);
}

function WGCalToolIsVisible( &$action,  $tool ) {
  $state = false;
  $all =  explode("|", $action->GetParam("WGCAL_U_TOOLSSTATE", ""));
  if (count($all)>0) {
    while (list($k, $v) = each($all)) {
      $t = explode("%",$v);
      if ($t[0] == $tool) {
	$state = $t[1];
      }
    }
  }
  if ($state==1) $state = true;
  return $state;
}

?>
