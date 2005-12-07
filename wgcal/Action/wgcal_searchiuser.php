<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchiuser.php,v 1.17 2005/12/07 10:21:43 marc Exp $
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
  
  $ifam = GetHttpVars("ifam", "");
  $itext = GetHttpVars("itext", "");

  $cuser = GetHttpVars("cuser", $action->user->fid);
  $proto = GetHttpVars("proto", "default");
  $limit = GetHttpVars("lim", 25);

  $sgrp = GetHttpVars("sgrp", 0); // 1 : Only user in my groups
  $sgrp = ($sgrp!=0 && $sgrp!=1 ? 0 : $sgrp);


  $iuserfam = getFamIdFromName($dbaccess, "IUSER");
  $filter[0] = "title ~* '".$itext."'";
  $tsfam = explode("|", $ifam);
  foreach ($tsfam as $k => $v) {
    if ($v=="") continue;
    if (!is_numeric($v)) $filter[1] .= ($filter[1]=="" ? "" : " OR ") . "fromid=".getFamIdFromName($dbaccess, $v)."";
    else $filter[1] .= ($filter[1]=="" ? "" : " OR ") . "fromid=$v";
  }
  $filter[1] = "( " . $filter[1] . " )";
  $rdoc = getChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, $limit, $filter,  $action->user->id, "TABLE");
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
