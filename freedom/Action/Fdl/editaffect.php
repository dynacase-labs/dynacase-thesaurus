<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2000 
 * @version $Id: editaffect.php,v 1.2 2006/08/01 15:20:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/editutil.php");

// -----------------------------------
// -----------------------------------
/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global viewdoc Http var : with preview of affect document [Y|N]
 */
function editaffect(&$action) {
  $docid = GetHttpVars("id"); 
  $viewdoc = (GetHttpVars("viewdoc","N")=="Y"); 
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_doc($dbaccess,$docid);
  editmode($action);

  $action->lay->Set("id",$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->set("VIEWDOC",$viewdoc);
  $action->lay->set("affecttitle",sprintf(_("Affectation for %s"),$doc->title));
  
}
?>