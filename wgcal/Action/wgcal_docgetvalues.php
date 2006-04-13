<?php
/**
 * View Document
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_docgetvalues.php,v 1.2 2006/04/13 17:16:03 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * View a js document
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 */
function wgcal_docgetvalues(&$action) {
  // -----------------------------------
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id");
  $latest = GetHttpVars("latest");
  $dbg = GetHttpVars("dbg", 0);

  if ($docid=="") {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", _("document reference no set"));
    return;
  }
   
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);

  if (intval($docid) == 0) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", _("unknow logical reference")." : ".$docid);
    return;
  }
    
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) {
    $action->lay->set("status", -1);
    $action->lay->set("statustext", sprintf(_("cannot see unknow reference %s"),$docid));
    return;
  }

  $dt = getTDoc($dbaccess, $docid);
  $ob = array();
  foreach ($dt as $k => $v ) {
    if ($k!='comment' && $k!='values' && $k!='attrids' ) $ob[] = array( "attr" => $k, "value" => addslashes(str_replace(array("\r","\n"),array("","\£"),$v)));
  }
  $action->lay->setBlockData("values", $ob);
  $action->lay->set("status", 0);
  $action->lay->set("statustext", addslashes($doc->getTitle()));

  $action->lay->set("dbgpre", "");
  $action->lay->set("dbgpost", "");  
  if ($dbg==1) {
   $action->lay->set("dbgpre", "<pre>");
   $action->lay->set("dbgpost", "</prev>");  
  }
}
?>
