<?php
/**
 * Search document
 *
 * @author Anakeen 2000 
 * @version $Id: search.php,v 1.25 2005/08/18 09:20:35 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Lib.Dir.php");

include_once("FDL/freedom_util.php");  



include_once("FDL/modcard.php");


/**
 * Search document 
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global viewone Http var : (Y|N) if Y direct view document detail if only one returned
 * @global view Http var : display mode : icon|column|list
 */
function search(&$action) {
  // -----------------------------------

  $docid = GetHttpVars("id",0);
  $classid=GetHttpVars("classid",0);
  $keyword=GetHttpVars("_se_key",GetHttpVars("keyword")); // keyword to search
  $viewone=GetHttpVars("viewone"); // direct view if only one Y|N
  $target=GetHttpVars("target"); // target window when click on document
  $view=GetHttpVars("view"); // display mode : icon|column|list
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($classid == 0) {
    if ($docid > 0) {
      $doc = new_Doc($dbaccess, $docid);
      $classid=$doc->fromid;
    }
    else {
      $classid=5;
      //      $action->exitError(_("kind of search is not defined"));
    }
  }
  // new doc
  $ndoc = createDoc($dbaccess, $classid);
  if (! $ndoc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
    
  if ($keyword != "") $ndoc->title=_("new search ").$keyword;
  else {
    $famid=GetHttpVars("_se_famid");
    if ($famid>0) {
      $fam=$ndoc->getTitle($famid);
      $ndoc->title=sprintf(_("search %s"),$fam);
    } else {
      $ndoc->title=sprintf(_("search result"));
    }
  }
  $ndoc->doctype='T';
  $ndoc->setValue("se_key",$keyword);
  $ndoc->setValue("se_latest","yes");
  $ndoc->setValue("se_famid",GetHttpVars("famid"));
  $err = $ndoc-> Add();
 
  if ($err != "")  $action->ExitError($err);

  SetHttpVar("id", $ndoc->id);
  $err = modcard($action, $ndocid); // ndocid change if new doc
    
  
  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&view=$view&target=$target&viewone=$viewone&dirid=".$ndoc->id,
	   $action->GetParam("CORE_STANDURL"));
  
  
}


?>