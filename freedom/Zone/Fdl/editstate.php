<?php
/**
 * State document edition
 *
 * @author Anakeen 2000 
 * @version $Id: editstate.php,v 1.8 2004/02/05 15:42:58 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

include_once("FDL/Class.WDoc.php");

include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/editutil.php");

// -----------------------------------
function editstate(&$action) {
  // -----------------------------------
  //print "<HR>EDITCARD<HR>";
  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
  $usefor = GetHttpVars("usefor"); // special uses
	  
	  
      
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  // ------------------------------------------------------
  //  new or modify ?
  if ($docid == 0)    {		
    if ($classid > 0) {
      $doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
      if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      $doc->id=$classid;
    }
	
  }  else    {      
	
	
    // when modification 
    $doc= new Doc($dbaccess,$docid);
	
  }
  
  if (($usefor!="D")&&($usefor!="Q")) {
  
  if ($doc->wid > 0) {
    // compute the changed state
    $wdoc = new Doc($dbaccess,$doc->wid);
    $wdoc->Set($doc);
    $fstate = $wdoc->GetFollowingStates();
    $action->lay->Set("initstatevalue",$doc->state );
    $action->lay->Set("initstatename", $action->text($doc->state) );
    $tstate= array();
    while (list($k, $v) = each($fstate)) {
      $tstate[$k]["statevalue"] = $v;
      $tstate[$k]["statename"] = _($v);
    }
    $action->lay->SetBlockData("NEWSTATE", $tstate);
    $action->lay->SetBlockData("TRSTATE", array(0=>array("boo")));
  } else {	
    $fdoc = $doc->getFamDoc();

    if ($fdoc->schar == "R") {
      $action->lay->SetBlockData("COMMENT", array(0=>array("boo")));
    }
    
  }
  }
  
  
  
  
}
?>
