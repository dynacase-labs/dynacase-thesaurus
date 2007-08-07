<?php
/**
 *  Insert documents in  folder
 *
 * @author Anakeen 2007
 * @version $Id: insertdocument.php,v 1.1 2007/08/07 14:46:07 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocFam.php");


/**
 * Insert documents in  folder
 * @param Action &$action current action
 * @global id Http var : folder document identificator to see
 */
function insertdocument(&$action) {
  
  $docid = GetHttpVars("id");
  $uchange = GetHttpVars("uchange");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  if ($docid=="") $action->exitError(_("no document reference"));
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);
  if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'"),GetHttpVars("id")));
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s"),$docid));
  if ($doc->defDoctype != 'D') $action->exitError(sprintf(_("not a static folder %s"),$doc->title));
  
  $err=$doc->control("modify");
  if ($err != "") $action->exitError($err);
  if ($doc->isLocked(true)) $action->exitError(sprintf(_("folder locked %s"),$doc->title));

  print_r2($uchange);
  foreach ($uchange as $initid=>$state) {
    if ($initid >0) {

    switch ($state) {      
    case "new":
      $err[$initid]=$doc->addFile($initid);
      break;
    case "deleted":
      $err[$initid]=$doc->delFile($initid);
      break;
    }
    }
  }
  print_r2($err);
}
?>