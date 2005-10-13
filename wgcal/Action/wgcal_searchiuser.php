<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchiuser.php,v 1.14 2005/10/13 09:29:32 marc Exp $
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
include_once("WGCAL/Lib.Agenda.php");


function wgcal_searchiuser(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess);
  
  $cuser = GetHttpVars("cuser", $action->user->fid);
  $proto = GetHttpVars("proto", "default");
  $limit = GetHttpVars("lim", 25);

  $sgrp = GetHttpVars("sgrp", 0); // 1 : Only user in my groups
  $sgrp = ($sgrp!=0 && $sgrp!=1 ? 0 : $sgrp);

  $iuserfam = getFamIdFromName($dbaccess, "IUSER");
  $filter[0] = "title ~* '".GetHttpVars("iusertext", "")."'";
  $filter[1] = "fromid=$iuserfam";
  $rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, $limit, $filter, 
		      $action->user->id, "TABLE", $iuserfam );
  $t = array(); $i = 0;
  foreach ($rdoc as $k => $v) {
    if ($cuser != $v["id"]) {
      if ($v["fromid"] == $iuserfam) {
	$cal = getUserPublicAgenda($v["id"], false);
	if ($cal && $cal->isAffected())  $writeaccess = ($cal->Control("invite")==""?true:false);
	else $writeaccess = false;
      } else $writeaccess = true;
      if ($writeaccess) {
	$t[$i]["attId"] = $v["id"];
	$t[$i]["attIcon"] = $doc->GetIcon($v["icon"]);
	$t[$i]["attTitle"] = ucwords(strtolower(addslashes(($v["title"]))));
	$t[$i]["attState"] = EVST_NEW;
	$t[$i]["attLabel"] = WGCalGetLabelState(EVST_NEW);
	$t[$i]["attColor"] = WGCalGetColorState(EVST_NEW);
	$t[$i]["attSelect"] = "true";
	
	$t[$i]["romode"] = ($writeaccess ? "false":"true");
	$i++;
      }
    }
  }
  if (count($t)>0) wUSort($t, "attTitle");
  $action->lay->set("proto", $proto);
  $action->lay->SetBlockData("LP$proto", $t);
}
?>
