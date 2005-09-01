<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchiuser.php,v 1.9 2005/09/01 16:48:27 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('FDL/Class.Doc.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.wTools.php");


function wgcal_searchiuser(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $families = getFamIdFromName($dbaccess, "IUSER");
  $doc = new Doc($dbaccess);
  
  $proto = GetHttpVars("proto", "default");
  $limit = GetHttpVars("lim", 25);

  $sgrp = GetHttpVars("sgrp", 0); // 1 : Only user in my groups
  $sgrp = ($sgrp!=0 && $sgrp!=1 ? 0 : $sgrp);

  $wre = GetHttpVars("wre", 1);      // 1 : Only Write enabled calendar
  $wre = ($wre!=0 && $wre!=1 ? 0 : $wre);

  $tg = wGetUserGroups();
  foreach ($tg as $k => $v) $mygroups[] = $k;


  $rdoc = wSearchUserCal(-1, $mygroups, $wre, GetHttpVars("iusertext", ""));
  
  $t = array(); $i = 0;
  foreach ($rdoc as $k => $v) {
    if ($action->user->id != $v["id"]) {
      $t[$i]["attId"] = $v["id"];
      $t[$i]["attIcon"] = $doc->GetIcon($v["icon"]);
      $t[$i]["attTitle"] = ucwords(strtolower(addslashes(($v["title"]))));
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
