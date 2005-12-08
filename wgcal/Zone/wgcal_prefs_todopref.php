<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_todopref.php,v 1.7 2005/12/08 15:57:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_prefs_todopref(&$action) {
  
  $optchk = array(
		  "ordertodo" => array(_("order todo "), 
					"WGCAL_U_TODOORDER", 
					"wgcal_toolbar", 
					"WGCAL_TOOLBAR", 
					array( "desc"  => _("todo asc"),
					       "asc"   => _("todo desc"))),
		  "seetodofor" => array(_("display todos for "), 
					"WGCAL_U_TODODAYS", 
					"wgcal_toolbar", 
					"WGCAL_TOOLBAR", 
					array( "3"  => "3 "._("days"),
					       "7"  => "1 "._("week"),
					       "14" => "2 "._("weeks") ,
					       "-1" => _("all todo"))),
		  "warntodofor" => array(_("warn for todo in the next "),
					 "WGCAL_U_TODOWARN", 
					 "wgcal_toolbar", 
					 "WGCAL_TOOLBAR",
					 array( "2"  => "2 "._("days"),
						"7"  => "1 "._("week") )),
		  "setdefdate" => array(_("set todo default date to today more "),
					"WGCAL_U_TODODEFLIMIT", 
					"wgcal_toolbar", 
					"WGCAL_TOOLBAR", 
					array( "1"  => "1 "._("days"),
					       "2"  => "2 "._("days"),
					       "3"  => "3 "._("days"),
					       "7"  => "1 "._("week") ))
		  );
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $uid = GetHttpVars("uid", $action->user->id);
  $toptchk = array(); 
  $io = 0;
  foreach ($optchk as $ko => $vo) {
    $cVal = $action->GetParam($vo[1]);
    $toptchk[$io]["iSel"] = $ko;
    $toptchk[$io]["iText"] = $vo[0];
    $toptchk[$io]["iVar"] = $vo[1];
    $toptchk[$io]["iVal"] = $cVal;
    $toptchk[$io]["iFrame"] = $vo[2];
    $toptchk[$io]["iAction"] = $vo[3];
    $toptchk[$io]["uid"] = $uid;
    $opt = array();
    foreach ($vo[4] as $k => $v) {
      $opt[] = array( "iOptText" => $v,
		      "iOptVal" => $k,
		      "iOptSel" => ($cVal==$k ? "selected" : ""));
    }
    $action->lay->SetBlockData("OPT".$ko, $opt);
    $io++;
  }
  $action->lay->SetBlockData("SELECT", $toptchk);

  return;
}
?>
