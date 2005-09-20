<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_delegate.php,v 1.3 2005/09/20 17:14:49 marc Exp $
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

  $dg_mail  = ($user->getValue("us_wgcal_dgmail", 0) == 1 ? true : false);
  $dg_uid   = $user->getTValue("us_wgcal_dguid");
  $dg_name  = $user->getTValue("us_wgcal_dguname");
  $dg_uwid  = $user->getTValue("us_wgcal_dguwid");
  $dg_umode = $user->getTValue("us_wgcal_dgumode");
  
  $duser = array();
  foreach ($dg_uid as $k => $v) {
    if ($v!="") {
      $duser[] = array( "durg" => $k,
			"duid" => $dg_uid[$k], 
			"duname" => ucwords(addSlashes($dg_name[$k])),
			"dall" => $dg_umode[$k] );
    }
  }
  $action->lay->set("uslimit", 3);
  $action->lay->set("dmail", $dg_mail);
  $action->lay->setBlockData("dadduser", $duser);
      
}

?>