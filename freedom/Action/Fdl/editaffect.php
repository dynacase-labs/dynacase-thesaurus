<?php
/**
 * Edition to affect document
 *
 * @author Anakeen 2000 
 * @version $Id: editaffect.php,v 1.1 2006/07/28 15:21:00 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");

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
  $viewdoc = (GetHttpVars("viewdoc","Y")=="Y"); 



  $action->lay->Set("id",$docid);
  $action->lay->Set("title",$doc->title);
  $action->lay->set("VIEWDOC",$viewdoc);
  
}
?>