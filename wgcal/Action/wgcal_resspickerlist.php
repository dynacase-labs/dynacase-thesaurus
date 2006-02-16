<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspickerlist.php,v 1.16 2006/02/16 15:46:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.wTools.php");
include_once("WGCAL/Lib.Agenda.php");


function wgcal_resspickerlist(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $limit = 25;
  $action->lay->set("limit", $limit);

  $target = GetHttpVars("updt", "");
  $families = GetHttpVars("sfam", "");
  $title    = GetHttpVars("stext", "");
  $wre = GetHttpVars("wre", 0);      // 1 : Only Write enabled calendar

  if ($families == "") return;

  $doc = new_Doc($action->GetParam("FREEDOM_DB"));
  $filter = array( );
  $if = 0;
  if ($title!="") $filter[0] = "title ~* '".$title."'";
  $fam = explode("|", $families);
  foreach ($fam as $kf => $vfi) { 
    if ($vfi == "" ) continue;
    $vf = (is_numeric($vfi) ? $vfi : getIdFromName($action->GetParam("FREEDOM_DB"), $vfi));
    if ($vf == "" ) continue;
    $filter[1] = "fromid=$vf";
    $rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, $limit, $filter, 
			  $action->user->id, "TABLE", $vf);
    foreach ($rdoc as $k => $v) {
      if ($action->user->fid != $v["id"]) {
	if (wIsFamilieInteractive($v["fromid"])) {
	  $writeaccess = $readaccess = false;
	  $cal = getUserPublicAgenda($v["id"], false);
	  if ($cal && $cal->isAffected()) {
	    $readaccess = ($cal->Control("execute")==""?true:false);
	    $writeaccess = ($cal->Control("invite")==""?true:false);
	  } 
	} else {
	  $writeaccess = $readaccess = true;
	}
	if ($writeaccess || ($readaccess && $wre==0)) {
	  $t[$v["id"]]["RESSID"] = $v["id"];
	  $t[$v["id"]]["RESSICON"] = $doc->GetIcon($v["icon"]);
	  $t[$v["id"]]["RESSTITLEJS"] = addslashes(ucwords(strtolower($v["title"])));
	  $t[$v["id"]]["RESSTITLE"] = ucwords(strtolower($v["title"]));
	  
	  $t[$v["id"]]["STATE"] = -1;
	  $t[$v["id"]]["TSTATE"] = "";
	  $t[$v["id"]]["CSTATE"] = "transparent";
	  $t[$v["id"]]["ROMODE"] = ($writeaccess?false:true);
	  $t[$v["id"]]["ROMODEV"] = ($writeaccess?"false":"true");
	  if (wIsFamilieInteractive($v["fromid"])) {
	    $t[$v["id"]]["STATE"] = EVST_NEW;
	    $t[$v["id"]]["TSTATE"] = WGCalGetLabelState(EVST_NEW);
	    $t[$v["id"]]["CSTATE"] = WGCalGetColorState(EVST_NEW);
	  }
	}
	}
    }
  }
  wUSort($t, "RESSTITLE");
  $action->lay->SetBlockData("RESSOURCES", $t);
}

?>
