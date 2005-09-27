<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_delegate.php,v 1.4 2005/09/27 15:29:36 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Class.Doc.php');

function wgcal_prefs_delegate(&$action) {


  if (!$action->HasPermission("WGCAL_VCAL")) {
    $action->lay->set("showdelegate", false);
    return;
  }

  $action->lay->set("showdelegate", true);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $user = new_Doc($dbaccess, $action->user->fid);
  $dcal = getUserPublicAgenda($user->id, false);

  $dg_mail  = ($dcal->getValue("agd_dmail", 0) == 1 ? true : false);
  $dg_fid   = $dcal->getTValue("agd_dfid");
  $dg_name  = $dcal->getTValue("agd_dname");
  $dg_wid   = $dcal->getTValue("agd_dwid");
  $dg_umode = $dcal->getTValue("agd_dmode");
  
  $duser = array();
  foreach ($dg_fid as $k => $v) {
    if ($v!="") {
      $duser[] = array( "durg" => $k,
			"duid" => $v, 
			"duname" => ucwords(addSlashes($dg_name[$k])),
			"dall" => $dg_umode[$k] );
    }
  }
  $action->lay->set("uslimit", 3);
  $action->lay->set("dmail", $dg_mail);
  $action->lay->setBlockData("dadduser", $duser);
      
}

?>