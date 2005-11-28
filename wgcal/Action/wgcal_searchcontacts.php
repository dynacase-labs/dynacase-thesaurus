<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchcontacts.php,v 1.1 2005/11/28 09:34:20 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('FDL/Class.Doc.php');
include_once("EXTERNALS/WGCAL_external.php");


// http://sn.marc.i-cesam.com/freedom/index.php?sole=Y&app=WGCAL&action=WGCAL_SEARCHCONTACTS&sfam=VEHICLE|USER|IUSER&stext=peu&cmode=R&smode=C&cfunc=toto
function wgcal_searchContacts(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $minLength = 3;
  $resultCountMax = 25;
  $iuserfam = getFamIdFromName($dbaccess, "IUSER");
  
  $sfam = GetHttpVars("sfam", "");
  $cFHandler = GetHttpVars("cfunc", "");
  $sText = GetHttpVars("stext", "");
  $sMode = GetHttpVars("smode", "B"); // [B]egins [C]ontains
  $calMode = GetHttpVars("cmode", "R"); // [R]ead [W]rite
  $iClass = GetHttpVars("iclass", ""); 
  
  $action->lay->set("noResult", true);
  if ($sfam=="" || strlen($sText)<$minLength) return;

  $filter = array();

  $tsfam = explode("|", $sfam);
  foreach ($tsfam as $k => $v) {
    if ($v=="") continue;
    if (!is_numeric($v)) $filter[0] .= ($filter[0]=="" ? "" : " OR ") . "fromid=".getFamIdFromName($dbaccess, $v)."";
    else $filter[0] .= ($filter[0]=="" ? "" : " OR ") . "fromid=$v";
  }
  $filter[0] = "( " . $filter[0] . " )";

  switch ($sMode) {
  case "C" : $sf = ""; break;
  default: $sf = "^";
  }
  $filter[1] = "title ~* '".$sf.$sText."'";
 
//   print_r2($filter);
  $tres = array();
  $rdoc = getChildDoc($dbaccess, 0, 0, $resultCountMax, $filter, $action->user->id, "TABLE");
//   print_r2($rdoc);
  if (count($rdoc)>0) {
    foreach ($rdoc as $k => $v) {
      if ($action->user->id == $v["id"]) continue;
      if ($v["fromid"] == $iuserfam && $cmode=="R") {
	$cal = getUserPublicAgenda($v["id"], false);
	if ($cal && $cal->isAffected())  $writeaccess = ($cal->Control("invite")==""?true:false);
	else $writeaccess = false;
      }
      else $writeaccess = true;
      if ($writeaccess) {
	$tres[] = array(
			"handlerSet" => ($cFHandler=="" ? false : true),
			"handler" => $cFHandler,
			"class" => $iClass,
			"id" => $v["id"],
			"fid" => $v["fromid"],
			"icon" => Doc::GetIcon($v["icon"]),
			"title" => ucwords(strtolower(addslashes(($v["title"])))) 
			);
      }
    }
    if (count($tres)>0) {
      $action->lay->set("noResult", false);
      $action->lay->setBlockData("result", $tres);
    }
  }
  return;
}