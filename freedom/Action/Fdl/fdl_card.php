<?php
/**
 * View Document
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_card.php,v 1.13 2005/08/10 10:15:32 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * View a document
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 * @global abstract Http var : (Y|N) if Y view only abstract attribute
 * @global props Http var : (Y|N) if Y view properties also
 * @global zonebodycard Http var : if set, view other specific representation
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink Http var : (Y|N)if N hyperlink are disabled
 * @global target Http var : is set target of hyperlink can change (default _self)
 * @global reload Http var : (Y|N) if Y update freedom folders in client navigator
 * @global dochead Http var :  (Y|N) if N don't see head of document (not title and icon)
 */
function fdl_card(&$action) {
  // -----------------------------------
  
  $docid = GetHttpVars("id");
  $latest = GetHttpVars("latest");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid=="") $action->exitError(_("no document reference"));
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);
  if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'"),GetHttpVars("id")));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  if (($latest == "Y") && ($doc->locked == -1)) {
    // get latest revision
    SetHttpVar("id",$doc->latestId());
  } 
  if (($latest == "L") && ($doc->lmodify != 'L')) {
    // get latest fixed revision
    SetHttpVar("id",$doc->latestId(true));
  }
  if (($latest == "P") && ($doc->revision > 0)) {
    // get previous fixed revision
    $pdoc = getRevTDoc($dbaccess, $doc->initid,$doc->revision-1);
    SetHttpVar("id",$pdoc["id"]);
  }


  $action->lay->Set("TITLE",$doc->title);
  $action->lay->Set("id",$docid);

  $listattr = $doc->GetActionAttributes();
  $taction=array();
  foreach ($listattr as $k => $v) {
    if ($v->mvisibility != "H") {
      if ($v->getOption("submenu")=="") {
	$taction[$k]=array("wadesc"=>$v->labelText,
			   "walabel"=>ucfirst($v->labelText),
			   "wtarget"=>$v->id,
			   "wlink"=>$v->getLink($doc->latestId()));
      }
    }
  }
  $action->lay->setBlockData("WACTION",$taction);
  $action->lay->set("VALTERN",($action->GetParam("FDL_VIEWALTERN","yes")=="yes"));
}

?>
