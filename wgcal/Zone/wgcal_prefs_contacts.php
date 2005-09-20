<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_contacts.php,v 1.6 2005/09/20 17:14:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');


function wgcal_prefs_contacts(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $uid = GetHttpVars("uid", $action->user->id);

  // Prefered contact list
  $tco = array();
  $used = $action->parent->param->GetUParam("WGCAL_U_USEPREFRESSOURCES", $uid, 1);
  $action->lay->set("usecontactstate", ($used==1?"checked":""));
  $contacts = $action->parent->param->GetUParam("WGCAL_U_PREFRESSOURCES", $uid, "");
  $tcontacts = explode("|", $contacts);
  if (count($tcontacts)>0) {
    foreach ($tcontacts as $kc => $vc) {
      if ($vc=="") continue;
      $rd = new_Doc($dbaccess, $vc);
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
