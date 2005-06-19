<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_addtoprefcals.php,v 1.1 2005/06/19 17:38:37 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_addtoprefcals(&$action) {

  $r = GetHttpVars("id", 0);
  if ($r>0) {
    $contacts = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
    $tcontacts = explode("|", $contacts);
    $rplist = $r;
    if (count($tcontacts)>0) {
      foreach ($tcontacts as $kc => $vc) {
	if ($vc=="" || $vc==$r) continue;
	$rplist .= (strlen($vc)>0?"|":"").$vc;
      }
    }
    $action->parent->param->set("WGCAL_U_PREFRESSOURCES", $rplist, 
				PARAM_USER.$action->user->id, $action->parent->id);
  }
  redirect($action, $action->parent->name, "WGCAL_HIDDEN");
}
?>