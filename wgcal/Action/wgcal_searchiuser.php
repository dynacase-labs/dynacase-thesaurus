<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchiuser.php,v 1.1 2005/03/22 18:30:05 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/WGCAL_external.php");


function wgcal_searchiuser(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $families = getFamIdFromName($dbaccess, "IUSER");
  $doc = new Doc($dbaccess);
  
  $filter = array( );
  $filter[] = "title ~* '".GetHttpVars("iusertext", "")."'";
  $rdoc = GetChildDoc($dbaccess, 0, 0, "ALL", $filter, $action->user->id, "TABLE", $families);
  $t = array(); $i = 0;
  foreach ($rdoc as $k => $v) {
    if ($action->user->id != $v["id"]) {
      $t[$i]["attId"] = $v["id"];
      $t[$i]["attIcon"] = $doc->GetIcon($v["icon"]);
      $t[$i]["attTitle"] = $v["title"];
      $t[$i]["attState"] = EVST_NEW;
      $t[$i]["attLabel"] = WGCalGetLabelState(EVST_NEW);
      $t[$i]["attColor"] = WGCalGetColorState(EVST_NEW);
      $t[$i]["attSelect"] = "true";
      $i++;
    }
  }
  if (count($t)==1) $action->lay->SetBlockData("RESSLIST", $t);
}
?>
