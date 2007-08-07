<?php
/**
 * Interface to inser document in  folder
 *
 * @author Anakeen 2007
 * @version $Id: editinsertdocument.php,v 1.1 2007/08/07 14:46:07 eric Exp $
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
 * @global id Http var : folder document identificator to see
 * @global famid Http var : family to use for search
 */
function editinsertdocument(&$action) {
  
  $docid = GetHttpVars("id");
  $famid = GetHttpVars("famid");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  if ($docid=="") $action->exitError(_("no document reference"));
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);
  if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'"),GetHttpVars("id")));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));
  if ($doc->defDoctype != 'D') $action->exitError(sprintf(_("not a static folder %s"),$doc->title));

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDC/Layout/inserthtml.js");
  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=EDITJS");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/editinsertdocument.js");
  

  $l=$doc->getContent();

  $action->lay->set("restrict",false);

  if (! $famid) {
    if (method_exists($doc,"isAuthorized")) {	
      if ($doc->isAuthorized($classid)) { 
	// verify if classid is possible
	if ($doc->norestrict) $tclassdoc=GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
	else {
	  $tclassdoc=$doc->getAuthorizedFamilies();
	  $action->lay->set("restrict",true);
	}
      } else  {
	$tclassdoc=$doc->getAuthorizedFamilies();
	$first = current($tclassdoc);
	$famid = $first["id"];
	$action->lay->set("restrict",true);
      }
    } else {
      $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
    }
    $action->lay->SetBlockData("SELECTCLASS", $tclassdoc);
  
    $action->lay->SetBlockData("SELECTCLASS", $tclassdoc);
    $action->lay->set("famid",false);
  } else {
    $action->lay->set("famid",$famid);
  }
  $action->lay->set("docid",$doc->id);

  $action->lay->setBlockData("CONTENT",$l);
  $action->lay->set("nmembers",sprintf(_("%d documents"),count($l)));
}
?>