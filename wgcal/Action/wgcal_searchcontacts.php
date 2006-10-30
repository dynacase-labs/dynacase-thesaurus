<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_searchcontacts.php,v 1.10 2006/10/30 17:11:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('FDL/Class.Doc.php');
include_once("WGCAL/Lib.wTools.php");
include_once("WGCAL/Lib.Agenda.php");

function wgcal_searchContacts(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $minLength = 1;
  $resultCountMax = 10;
  $iuserfam = getFamIdFromName($dbaccess, "IUSER");
  
  $sfam = GetHttpVars("sfam", "");
  $cFHandler = GetHttpVars("cfunc", "");
  $sText = GetHttpVars("stext", "");
  $sMode = GetHttpVars("smode", "B"); // [B]egins [C]ontains
  $calMode = GetHttpVars("cmode", "R"); // [R]ead [W]rite
  $iClass = GetHttpVars("iclass", ""); 
  
  $action->lay->set("count", 0);
  $action->lay->set("oneResult", false);
  $action->lay->set("moreResult", false);
  $action->lay->set("moreResultT", "");

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
 
  $action->lay->set("moreResultT", "");
  $tres = array();
  $rdoc = getChildDoc($dbaccess, 0, 0, ($resultCountMax+1), $filter, $action->user->id, "TABLE");
  if (count($rdoc)>0) {
    if (count($rdoc)>$resultCountMax) {
      $action->lay->set("moreResultT", sprintf(_("only %s first"), $resultCountMax));
    }
    $ci = 0;
    foreach ($rdoc as $k => $v) {
      if ($ci>=$resultCountMax) continue;
      if ($action->user->id == $v["id"]) continue;
      $inter = wIsFamilieInteractive($v["fromid"]);
      if ($inter && $calMode=="W") {
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
			"fid" => ($inter?"true":"false"),
			"icon" => Doc::GetIcon($v["icon"]),
			"title" => ucwords(strtolower((str_replace(" ", "&nbsp;", $v["title"])))),
			"jstitle" => ucwords(strtolower(addslashes($v["title"]))),
			);
      }
      $ci++;
    }    
  }

  $nr = count($tres);
  $action->lay->set("count", $nr);
  if ($nr==1) {
    $action->lay->set("oneResult", true);
    $action->lay->setBlockData("result", $tres);
  } else if ($nr>1) {
    $action->lay->set("moreResult", true);
    $action->lay->setBlockData("result", $tres);
  }
  return;
}