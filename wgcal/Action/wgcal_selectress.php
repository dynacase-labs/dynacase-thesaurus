<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_selectress.php,v 1.1 2004/11/26 18:52:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


function wgcal_selectress(&$action) {
{

  // set to -1 to reset the ressource selection
  $rid = GetHttpVars("rid", -1);

  $nlress = "";
  if ($rid>0) {
    $lress = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", ""));
    $lress[count($lress)] = $rid."^0^blue"; 
    while (list($k,$v) = each($lress)) {
      if ($v!="") $nlress .= $v."|"; 
    }
  } else {
    $nlress = $action->user->id;
  }   
  $action->parent->param->set("WGCAL_U_RESSDISPLAYED", $nlress, 
                                PARAM_USER.$action->user->id, $action->parent->id);
  redirect($action, $action->parent->name, "WGCAL_CALENDAR");
  
}
?>