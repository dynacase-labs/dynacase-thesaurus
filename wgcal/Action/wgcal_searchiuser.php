<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchiuser.php,v 1.6 2005/08/25 16:02:16 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.wTools.php");


function wgcal_searchiuser(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $families = getFamIdFromName($dbaccess, "IUSER");
  $doc = new Doc($dbaccess);
  
  $proto = GetHttpVars("proto", "default");
  $limit = GetHttpVars("lim", 25);
  $filter = array( );
  $filter[] = "title ~* '".GetHttpVars("iusertext", "")."'";
  $rdoc = GetChildDoc($dbaccess, 0, 0, $limit, $filter, $action->user->id, "TABLE", $families);
  $t = array(); $i = 0;
  foreach ($rdoc as $k => $v) {
    if ($action->user->id != $v["id"]) {
      $t[$i]["attId"] = $v["id"];
      $t[$i]["attIcon"] = $doc->GetIcon($v["icon"]);
      $t[$i]["attTitle"] = ucwords(strtolower($v["title"]));
      $t[$i]["attState"] = EVST_NEW;
      $t[$i]["attLabel"] = WGCalGetLabelState(EVST_NEW);
      $t[$i]["attColor"] = WGCalGetColorState(EVST_NEW);
      $t[$i]["attSelect"] = "true";
      $i++;
    }
  }
  if (count($t)>0) wUSort($t, "attTitle");
  $action->lay->set("proto", $proto);
  $action->lay->SetBlockData("LP$proto", $t);
}
?>
