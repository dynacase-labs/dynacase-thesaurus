<?php

// ---------------------------------------------------------------
// $Id: freedom_edit.php,v 1.21 2003/07/25 12:43:18 eric Exp $
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

include_once("FDL/Class.WDoc.php");

include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");
include_once("VAULT/Class.VaultFile.php");

// -----------------------------------
function freedom_edit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
  $usefordef = GetHttpVars("usefordef"); // default values for a document




  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
   
  if ($docid > 0) {
    $doc= new Doc($dbaccess,$docid);
    if (! $doc->isAlive()) $action->exitError(sprintf(_("document id %d not found"),$docid));
    $cdoc =  $doc->getFamDoc();
    $tclassdoc[$doc->fromid] = array("id"=> $cdoc->id,
				     "title"=>$cdoc->title);
  } else {
    // new document select special classes
    if ($dirid > 0) {
      $dir = new Doc($dbaccess, $dirid);
      if (method_exists($dir,"getAuthorizedFamilies")) {

	$tclassdoc=$dir->getAuthorizedFamilies($classid);

	// verify if classid is possible
	if (! isset($tclassdoc[$classid])) {
	  $first = current($tclassdoc);
	  $classid = $first["id"];
	  setHttpVar("classid",$classid); // propagate to subzones
	}
      }
      else {
	$tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
      }
    } else {
      $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid,"TABLE");
    }

  }

  // when modification 
  if (($classid == 0) && ($docid != 0) ) $classid=$doc->fromid;

  
    

  // build list of class document

  $selectclass=array();
  
 
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["id"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    $selectclass[$k]["selected"]="";
  }

  // add no inherit for class document
  if (($docid > 0) && ($doc->doctype=="C")) {
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
      if ($usefordef=="Y") $action->lay->Set("TITLE", _("default values"));
      $action->lay->Set("editaction", $action->text("create"));
      if ($classid > 0) {
	$doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
	if ($doc === false) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
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
      if (! $doc->isAlive()) $action->ExitError(_("document not referenced"));

      $err = $doc->lock(true); // autolock
      if ($err != "")   $action->ExitError($err);
  

      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", $action->text("Validate"));
      
      // selected the current class document
      while (list($k,$cdoc)= each ($selectclass)) {	
	if ($doc->fromid == $selectclass[$k]["idcdoc"]) {	  
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
    
  $action->lay->Set("iconsrc", $doc->geticon());
 
  // compute the changed state
  $tstate= array();
  if ($doc->wid > 0) {
    $wdoc = new Doc($dbaccess,$doc->wid);
    $wdoc->Set($doc);
    $fstate = $wdoc->GetFollowingStates();
    $action->lay->Set("initstatevalue",$doc->state );
    while (list($k, $v) = each($fstate)) {
      $tstate[$k]["statevalue"] = $v;
      $tstate[$k]["statename"] = _($v);
    }
  }
  $action->lay->SetBlockData("NEWSTATE", $tstate);

  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);
  if ($docid > 0) $action->lay->Set("doctype", $doc->doctype);


  // sort by classname
  uasort($selectclass, "cmpselect");
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);


 
    

}
function cmpselect ($a, $b) {
  return strcasecmp($a["classname"], $b["classname"]);
}


?>
