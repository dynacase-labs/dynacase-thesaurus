<?php

// ---------------------------------------------------------------
// $Id: freedom_edit.php,v 1.5 2002/07/23 13:34:38 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_edit.php,v $
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

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocValue.php");

include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
function freedom_edit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc




  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
   


  if ($docid > 0) $doc= new Doc($dbaccess,$docid);

  // when modification 
  if (($classid == 0) && ($docid != 0) ) $classid=$doc->fromid;

  
    

  // build list of class document
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");

  $selectclass=array();
  $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid);
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
    $selectclass[$k]["selected"]="";
  }

  // add no inherit for class document
  if ($doc->doctype=="C") {
      $selectclass[$k+1]["idcdoc"]="0";
      $selectclass[$k+1]["classname"]=_("no document type");
  }

  if ($docid == 0)
    {
      switch ($classid) {
	case 2:
	  $action->lay->Set("TITLE", _("new directory"));
	  $action->lay->Set("refreshfld", "yes");
	break;
	case 3:	  
	case 4:	  
	  $action->lay->Set("TITLE", _("new profile"));
	break;
      default:
	$action->lay->Set("TITLE", _("new document"));
      }
      $action->lay->Set("editaction", $action->text("create"));
      if ($classid > 0) {
	$doc=new Doc($dbaccess,$classid); // the doc inherit from chosen class
      }
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($classid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
  else
    {     
      $err = $doc->CanUpdateDoc();
      if ($err != "")   $action->ExitError($err);
      if (! $doc->isAffected()) $action->ExitError(_("document not referenced"));
  

      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", $action->text("modify"));
      
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($doc->fromid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
    

 
  // compute the changed state
  $fstate = $doc->GetFollowingStates();
  $tstate= array();
      $action->lay->Set("initstatevalue",$doc->state );
  while (list($k, $v) = each($fstate)) {
    $tstate[$k]["statevalue"] = $v;
    $tstate[$k]["statename"] = _($v);
  }
  $action->lay->SetBlockData("NEWSTATE", $tstate);

  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);
  if ($docid > 0) $action->lay->Set("doctype", $doc->doctype);
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);


 
    

}
?>
