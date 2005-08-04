<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspickerlist.php,v 1.8 2005/08/04 10:13:48 marc Exp $
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

  if ($families == "") return;

  $doc = new Doc($action->GetParam("FREEDOM_DB"));
  $filter = array( );
  $if = 0;
  if ($title!="") $filter[$if++] = "title ~* '".$title."'";
  $fam = explode("|", $families);
  foreach ($fam as $kf => $vf) { 
    if ($vf == "" ) continue;
    $rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, "ALL", $filter, 
			$action->user->id, "TABLE", $vf);
    foreach ($rdoc as $k => $v) {
      if ($action->user->id != $v["id"]) {
	$t[$v["id"]]["RESSID"] = $v["id"];
	$t[$v["id"]]["RESSICON"] = $doc->GetIcon($v["icon"]);
	$t[$v["id"]]["RESSTITLEJS"] = addslashes(ucwords(strtolower($v["title"])));
	$t[$v["id"]]["RESSTITLE"] = ucwords(strtolower($v["title"]));
	$t[$v["id"]]["STATE"] = EVST_NEW;
	$t[$v["id"]]["TSTATE"] = WGCalGetLabelState(EVST_NEW);
	$t[$v["id"]]["CSTATE"] = WGCalGetColorState(EVST_NEW);
      }
    }
  }
  wUSort($t, "RESSTITLE");
  $action->lay->SetBlockData("RESSOURCES", $t);
}

?>
