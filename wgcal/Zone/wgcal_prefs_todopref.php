<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_todopref.php,v 1.1 2005/03/30 10:04:42 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/WGCAL_external.php");


function wgcal_prefs_todopref(&$action) {
  
  $optchk = array(
		  "seetodofor" => array(_("display todos for ... days"), 
					"WGCAL_U_TODODAYS", 
					"wgcal_toolbar", 
					"WGCAL_TOOLBAR", 
					"val" => array( "2", "7", "10", "14") ),
		  "warntodofor" => array(_("warn for todo in the next ... days"),
					 "WGCAL_U_TODOWARN", 
					 "wgcal_toolbar", 
					 "WGCAL_TOOLBAR",
					 "val" => array( "1", "2", "3", "7") ),
		  "setdefdate" => array(_("set todo default date to today + ... days"),
					"WGCAL_U_TODODEFLIMIT", 
					"wgcal_toolbar", 
					"WGCAL_TOOLBAR", 
					"val" => array( "0", "1", "2", "7"))
		  );
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $toptchk = array(); 
  $io = 0;
  foreach ($optchk as $ko => $vo) {
    $toptchk[$io]["iSel"] = $ko;
    $toptchk[$io]["iText"] = $vo[0];
    $toptchk[$io]["iVar"] = $vo[1];
    $toptchk[$io]["iFrame"] = $vo[2];
    $toptchk[$io]["iAction"] = $vo[3];

    $opt = array(); 
    $vsel =  $action->GetParam($vo[1]);
    foreach ($vo["val"] as $kv => $vv) {
      $opt[] = array( "iOptVal" => $vv, 
		      "iOptSelected" =>  ($vv==$vsel?"selected":""),
		      "iOptText" => $vv );
    }
    $toptchk[$io]["OPT".$ko] = $opt;
    $io++;
  }
  print_r2($toptchk);
  $action->lay->SetBlockData("SELECT", $toptchk);

  return;
}
?>
