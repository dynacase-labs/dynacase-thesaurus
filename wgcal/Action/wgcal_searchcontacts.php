<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchcontacts.php,v 1.3 2005/12/05 17:12:32 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('FDL/Class.Doc.php');
include_once("EXTERNALS/WGCAL_external.php");


function wgcal_searchContacts(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $minLength = 3;
  $resultCountMax = 10;
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
 
  $tres = array();
  $rdoc = getChildDoc($dbaccess, 0, 0, ($resultCountMax+1), $filter, $action->user->id, "TABLE");
  if (count($rdoc)>0) {
    $action->lay->set("noResult", true);
    $action->lay->set("moreResult", false);
    $ci = 0;
    if (count($rdoc)>$resultCountMax) {
      $action->lay->set("moreResult", true);
      $action->lay->set("moreResultT", sprintf(_("only %s first"), $resultCountMax));
    }
    foreach ($rdoc as $k => $v) {
      if ($ci>=$resultCountMax) continue;
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
			"title" => ucwords(strtolower(addslashes(str_replace(" ", "&nbsp;", $v["title"])))),
			);
      }
      $ci++;
    }
    if (count($tres)>0) {
      $action->lay->set("noResult", false);
      $action->lay->setBlockData("result", $tres);
    }
  }
  return;
}