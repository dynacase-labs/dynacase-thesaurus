<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_contacts.php,v 1.3 2005/08/03 16:35:13 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_prefs_contacts(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $uid = GetHttpVars("uid", $action->user->id);
  $tco = array();
  $used = $action->parent->param->GetUParam("WGCAL_U_USEPREFRESSOURCES", $uid, 1);
  $action->lay->set("usecontactstate", ($used==1?"checked":""));
  $contacts = $action->parent->param->GetUParam("WGCAL_U_PREFRESSOURCES", $uid, "");
  $tcontacts = explode("|", $contacts);
  if (count($tcontacts)>0) {
    foreach ($tcontacts as $kc => $vc) {
      if ($vc=="") continue;
      $rd = new Doc($dbaccess, $vc);
      if ($rd->IsAffected() && $rd->id != $action->user->fid) {
	$tco[$kc]["RDESCR"]= ucwords(strtolower($rd->title));
	$tco[$kc]["RID"]= $rd->id;
	$tco[$kc]["RICON"]= $rd->getIcon();
      }
    }
  }
  $action->lay->SetBlockData("L_RESS", $tco);
  return;
}
?>
