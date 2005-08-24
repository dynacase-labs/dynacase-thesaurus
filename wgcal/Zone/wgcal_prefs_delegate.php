<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_delegate.php,v 1.1 2005/08/24 17:37:00 marc Exp $
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

  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $user = new Doc($dbaccess, $action->user->fid);

  $dg_uid   = $user->getTValue("us_wgcal_dguid");
  $dg_name  = $user->getTValue("us_wgcal_dguname");
  $dg_uwid  = $user->getTValue("us_wgcal_dguwid");
  $dg_umode = $user->getTValue("us_wgcal_dgumode");
  
  $duser = array();
  foreach ($dg_uid as $k => $v) {
    if ($v!="") {
      $duser[] = array( "duid" => $dg_uid[$k], 
			"dujsname" => addSlashes($dg_name[$k]),
			"dall" => ($dg_umode[$k] == 1 ? true : false),
			"duname" => addSlashes($dg_name[$k]) );
    }
  }
  $action->lay->setBlockData("duser", $duser);
  $action->lay->setBlockData("djsuser", $duser);
      
}

?>