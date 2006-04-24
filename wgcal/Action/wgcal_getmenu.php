<?php
/**
 * Get event producter popup menu
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_getmenu.php,v 1.2 2006/04/24 15:50:31 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");
  include_once("FDL/popupdoc.php");

/**
 * get menu for event producer in JS (like mcalmenu needed)
 * @param Action &$action current action
 * @global id Http Var : document identificator 
 * @global latest Http var : (Y|N|L|P) if Y force view latest revision, L : latest fixed revision, P : previous revision
 */
function wgcal_getmenu(&$action) {
  // -----------------------------------
  include_once("WGCAL/Lib.wTools.php");
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id");
  $ctx   = GetHttpVars("ctx");
  $occ   = date("d/m/Y", GetHttpVars("occ"));

  if ($docid=="")  return _("document reference no set");
  if (! is_numeric($docid)) $docid=getIdFromName($dbaccess,$docid);
  if (intval($docid) == 0) return _("unknow logical reference");
    
  $doc = new_Doc($dbaccess, $docid);
  if (! $doc->isAffected()) return  sprintf(_("cannot see unknow reference %s"),$docid);

  switch ($ctx) {
  default:
    if (method_exists($doc, "agendaMenu")) $menudesc = $doc->agendaMenu($occ);
  }
  popupdoc($action, $menudesc["main"], $menudesc["sub"]);
}
?>
