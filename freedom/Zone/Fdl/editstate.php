<?php

// ---------------------------------------------------------------
// $Id: editstate.php,v 1.1 2002/07/30 12:32:29 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/editstate.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

//
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

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
	  
	  
      
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  // ------------------------------------------------------
    //  new or modify ?
      if ($docid == 0)    {		
	if ($classid > 0) {
	  $doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
	    $doc->id=$classid;
	}
	
      }  else    {      
	
	
	// when modification 
	  $doc= new Doc($dbaccess,$docid);
	
      }
  

  
  
  if (count( $doc->transitions) > 0) {
    // compute the changed state
      $fstate = $doc->GetFollowingStates();
    $action->lay->Set("initstatevalue",$doc->state );
    $action->lay->Set("initstatename", $action->text($doc->state) );
    $tstate= array();
    while (list($k, $v) = each($fstate)) {
      $tstate[$k]["statevalue"] = $v;
      $tstate[$k]["statename"] = _($v);
    }
    $action->lay->SetBlockData("NEWSTATE", $tstate);
    $action->lay->SetBlockData("TRSTATE", array(0=>array("boo")));
  }
  
  
  
  
  
}
?>
