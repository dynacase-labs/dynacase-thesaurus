<?php
/**
 * View Document
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_card.php,v 1.4 2004/05/06 08:08:00 eric Exp $
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
 * @global Http $docid document identificator to see
 * @global latest Http var : (Y|N) if Y force view latest revision
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

  if (intval($docid) == 0) $action->exitError(sprintf(_("reference must be a number not '%s'"),$docid));
  $doc = new Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));

  if (($latest == "Y") && ($doc->locked == -1)) {
    // get latest revision
    SetHttpVar("id",$doc->latestId());
  }


  $action->lay->Set("TITLE",$doc->title);
}

?>
