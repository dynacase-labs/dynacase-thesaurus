<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_contacts.php,v 1.1 2005/02/17 07:11:46 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/WGCAL_external.php");


function wgcal_prefs_contacts(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $tco = array();
  $used = $action->GetParam("WGCAL_U_USEPREFRESSOURCES", 1);
  $action->lay->set("usecontactstate", ($used==1?"checked":""));
  $contacts = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
  $tcontacts = explode("|", $contacts);
  if (count($tcontacts)>0) {
    foreach ($tcontacts as $kc => $vc) {
      if ($vc=="") continue;
      $rd = new Doc($dbaccess, $vc);
      if ($rd->IsAffected() && $rd->id != $action->user->fid) {
	$tco[$kc]["RDESCR"]= $rd->title;
	$tco[$kc]["RID"]= $rd->id;
	$tco[$kc]["RICON"]= $rd->getIcon();
      }
    }
  }
  $action->lay->SetBlockData("L_RESS", $tco);
  return;
}
?>
