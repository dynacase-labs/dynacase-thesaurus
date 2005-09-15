<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspickerlist.php,v 1.10 2005/09/15 15:58:11 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.wTools.php");


function wgcal_resspickerlist(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $target = GetHttpVars("updt", "");
  $families = GetHttpVars("sfam", "");
  $title    = GetHttpVars("stext", "");
  $wre = GetHttpVars("wre", 0);      // 1 : Only Write enabled calendar

  if ($families == "") return;

  $doc = new Doc($action->GetParam("FREEDOM_DB"));
  $filter = array( );
  $if = 0;
  if ($title!="") $filter[$if++] = "title ~* '".$title."'";
  $fam = explode("|", $families);
  foreach ($fam as $kf => $vf) { 
    if ($vf == "" ) continue;
    if ($vf=="IUSER" || $vf==getIdFromName($action->GetParam("FREEDOM_DB"), "IUSER")) {
      $tg = wGetUserGroups();
      foreach ($tg as $k => $v) $mygroups[] = $k;
      $rdoc = wSearchUserCal(-1, $mygroups, $wre, $title);
    } else {
      $rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, "ALL", $filter, 
			  $action->user->id, "TABLE", $vf);
    }
    foreach ($rdoc as $k => $v) {
      if ($action->user->id != $v["id"]) {
	$t[$v["id"]]["RESSID"] = $v["id"];
	$t[$v["id"]]["RESSICON"] = $doc->GetIcon($v["icon"]);
	$t[$v["id"]]["RESSTITLEJS"] = addslashes(ucwords(strtolower($v["title"])));
	$t[$v["id"]]["RESSTITLE"] = ucwords(strtolower($v["title"]));
	$t[$v["id"]]["STATE"] = ($v["fromid"]==128 ? EVST_NEW : -1);
	$t[$v["id"]]["TSTATE"] = ($v["fromid"]==128 ? WGCalGetLabelState(EVST_NEW) : "");
	$t[$v["id"]]["CSTATE"] = ($v["fromid"]==128 ? WGCalGetColorState(EVST_NEW) : "transparent");
	$t[$v["id"]]["ROMODE"] = "true";
      }
    }
  }
  wUSort($t, "RESSTITLE");
  $action->lay->SetBlockData("RESSOURCES", $t);
}

?>
