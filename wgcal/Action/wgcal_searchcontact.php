<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchcontact.php,v 1.3 2005/09/20 17:14:49 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_searchcontact(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess);
  
  $filter = array( );
  $filter[] = "title ~* '".GetHttpVars("contacttext", "")."'";
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", "IUSER");
  $t = array(); $i = 0;
  foreach ($rdoc as $k => $v) {
    if (!isset($t[$v["title"]])) {
//         print_r2($v);
      $t[$v["title"]]["icontact"] = $i++;
      $t[$v["title"]]["title"] = $v["title"] . gattr($v, "us_society"," (", ")");
      $t[$v["title"]]["icon"] = $doc->GetIcon($v["icon"]);
      $t[$v["title"]]["phone"] = gattr($v, "us_phone");
      $t[$v["title"]]["havephone"] = ($t[$v["title"]]["phone"] != "" ? true : false );
      $t[$v["title"]]["pphone"] = gattr($v, "us_pphone");
      $t[$v["title"]]["havepphone"] = ($t[$v["title"]]["pphone"] != "" ? true : false );
      $t[$v["title"]]["mail"] = gattr($v, "us_mail");
      $t[$v["title"]]["havemail"] = ($t[$v["title"]]["mail"] != "" ? true : false );
    }
  }
  $action->lay->SetBlockData("CONTACTS", $t);
}

function gattr(&$t, $att, $pre="", $post="") {
  return (isset($t[$att]) && $t[$att]!="" ? $pre.$t[$att].$post:"");
}
?>