<?php

// ---------------------------------------------------------------
// $Id: editbodycard.php,v 1.3 2002/07/17 13:35:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/Attic/editbodycard.php,v $
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
function editbodycard(&$action) {
  // -----------------------------------
    //print "<HR>EDITCARD<HR>";
  // Get All Parameters
    $docid = GetHttpVars("id",0);        // document to edit
      $classid = GetHttpVars("classid",0); // use when new doc or change class
	$dirid = GetHttpVars("dirid",0); // directory to place doc if new doc
	  
	  
	  // Set the globals elements
	    
	    $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  
  
  
  
  // ------------------------------------------------------
    //  new or modify ?
      if ($docid == 0)    {
	
	// new document
	  switch ($classid) {
	  case 2:
	    $action->lay->Set("TITLE", _("new directory"));
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
	  $cdoc= new Doc($dbaccess,$classid);
	  $doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
	    $doc->id=$classid;
	}
	
      }  else    {      
	
	
	// when modification 
	  $doc= new Doc($dbaccess,$docid);
	if ($classid == 0)    $classid=$doc->fromid;
	$err = $doc->CanLockFile()  ;
	if ($err != "")	$err=$doc->CanUpdateDoc();
	if ($err != "")   $action->ExitError($err);
	if (! $doc->isAffected()) $action->ExitError(_("document not referenced"));
	
	
	$action->lay->Set("TITLE", $doc->title);
	$action->lay->Set("editaction", $action->text("modify"));
	
      }
  
  // ------------------------------------------------------
    // build list of class document
      $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");
  
  $selectclass=array();
  
  $tclassdoc = $doc->GetClassesDoc($classid);
  while (list($k,$icdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$icdoc->initid;
    $selectclass[$k]["classname"]=$icdoc->title;
    $selectclass[$k]["selected"]="";
  }
  
  // add no inherit for class document
    if ($doc->doctype=="C") {
      $selectclass[$k+1]["idcdoc"]="0";
      $selectclass[$k+1]["classname"]=_("no document type");
    }
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);
  
  // selected the current class document
    while (list($k,$icdoc)= each ($selectclass)) {	
      if ($classid == $selectclass[$k]["idcdoc"]) {	  
	$selectclass[$k]["selected"]="selected";
      }
    }
  
  
  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("classid", $classid);
  
  
  
  // ------------------------------------------------------
    // Perform SQL search for doc attributes
      // ------------------------------------------------------
	
	
	
	
	
	$bdattr = new DocAttr($dbaccess);
  
  
  
  
  
  //$frames= $query->Query(0,0,"TABLE","select distinct frametext from DocAttr" );
  $frames=array();
  $listattr = $doc->GetAttributes(true);
  
  
  
  $nattr = count($listattr); // number of attributes
    
    
    $k=0; // number of frametext
      $v=0;// number of value in one frametext
	$currentFrameId="";
  $changeframe=false;
  $ih = 0; // index for hidden values
    $thidden =array();
  $tableframe=array();
  for ($i=0; $i < $nattr + 1; $i++)
    {
      // Compute value elements
	if ($i < $nattr)
	  {
	    
	    if ($docid > 0) $value = $doc->GetValue($listattr[$i]->id);
	    else $value = $cdoc->GetValue($listattr[$i]->id);
	    
	    
	    if ( $currentFrameId != $listattr[$i]->frameid) {
	      if ($currentFrameId != "") $changeframe=true;
	    }
	    
	  }
      
      
      if (($i == $nattr) ||  // to generate final frametext
	  $changeframe)
	{
	  $changeframe=false;
	  if ($v > 0 ) // one value detected
	    {
	      
	      $frames[$k]["frametext"]="[TEXT:".$doc->GetLabel($currentFrameId)."]";
	      $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
	      $action->lay->SetBlockData($frames[$k]["TABLEVALUE"],
					 $tableframe);
	      unset($tableframe);
	      $tableframe=array();
	      $k++;
	    }
	  $v=1;
	}
      
      
      //------------------------------
	// Set the table value elements
	  if (($i < $nattr) && ($listattr[$i]->type != "frame"))
	    {
	      
	      $currentFrameId = $listattr[$i]->frameid;
	      if ( ($listattr[$i]->visibility == "H") || 
		  ($listattr[$i]->visibility == "R") && (substr_count($listattr[$i]->type,"text") > 0)) {
		// special case for hidden values
		  $thidden[$ih]["hname"]= "_".$listattr[$i]->id;
		$thidden[$ih]["hid"]= $listattr[$i]->id;
		$thidden[$ih]["hvalue"]=chop(htmlentities($value));
		$ih++;
	      } else {
		$tableframe[$v]["value"]=chop(htmlentities($value));
		$label = $doc->GetLabel($listattr[$i]->id);
		$tableframe[$v]["attrid"]=$listattr[$i]->id;
		$tableframe[$v]["name"]=chop("[TEXT:".$label."]");

		if ($listattr[$i]->visibility == "N") $tableframe[$v]["labelclass"]="FREEDOMLabelNeeded";
		else $tableframe[$v]["labelclass"]="FREEDOMLabel";

		//$tableframe[$v]["name"]=$action->text($label);
		$tableframe[$v]["inputtype"]=getHtmlInput($action, 
							  $doc->id,
							  $listattr[$i]->id, 
							  $listattr[$i]->type, 
							  $listattr[$i]->visibility, 
							  $value);
		
		
		
		
		$v++;
		
	      }
	    }
    }
  
  // Out
    
    $action->lay->SetBlockData("HIDDENS",$thidden);
  $action->lay->SetBlockData("TABLEBODY",$frames);
  
  
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
