<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_selectress.php,v 1.2 2004/12/03 16:25:12 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


function wgcal_selectress(&$action) {

  // set to -1 to reset the ressource selection
  $rid = GetHttpVars("rid", -1);
  $sid = GetHttpVars("sid", -1);
  
  $found = 0;
  $nlress = "";
  if ($rid>0) {
    $lress = explode("|", $action->Read("WGCAL_RESSOURCES", ""));
    foreach ($lress as $kr => $vr) {
      $thisr = explode("%", $vr);
      if ($thisr[0] != "") {
	$col = ($thisr[2]=="" ? "blue" : $thisr[2]);
	if ($thisr[0] == $rid) $nlress .= $rid."%".$sid."%".$col."|";
	else $nlress .= $thisr[0]."%".$thisr[1]."%".$col."|";
      }
    }
    $action->Register("WGCAL_RESSOURCES", $nlress);
  }
  redirect($action, $action->parent->name, "WGCAL_CALENDAR");
  
}
?>