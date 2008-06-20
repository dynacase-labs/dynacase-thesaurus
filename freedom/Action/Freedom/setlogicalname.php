<?php
/**
 * Enable/disable forum for documents
 *
 * @author Anakeen 2000 
 * @version $Id: setlogicalname.php,v 1.1 2008/06/20 14:33:56 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



include_once("FDL/Class.Doc.php");

function setlogicalname(&$action)  {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id");
  $name =GetHttpVars("name");
  if ($docid && $name) {    
    $doc = new_Doc($dbaccess, $docid, true);
    if (! $doc->isAffected()) $action->addWarningMsg(sprintf(_("cannot see unknow reference %s"),$docid));
    else {
      if ($doc->name != "") {
	$action->addWarningMsg(sprintf(_("Logical name %s already set for %s"),$name,$doc->title));
      } else {
	  // verify not use yet
	  $q=$doc->exec_query("select id from doc where name='".pg_escape_string($name)."'");
	  if ($doc->numrows()==0) {
	    $doc->name=$name;
	    $err=$doc->modify(true,array("name"),true);
	    if ($err!="") $action->addWarningMsg($err);
	  } else {	    
	    $action->addWarningMsg(sprintf(_("Logical name %s already use other document"),$name,$doc->title));	    
	  }
      }
    }
  }
  
  redirect($action, "FDL", "IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=".$docid);
}
?>