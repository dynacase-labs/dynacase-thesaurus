<?php

// ---------------------------------------------------------------
// $Id: editbodycard.php,v 1.11 2002/09/30 11:46:44 eric Exp $
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

    $action->lay->Set("editaction", $action->text("create"));

    if ($classid > 0) {
      $cdoc= new Doc($dbaccess,$classid);
      $action->lay->Set("TITLE", sprintf(_("new %s"),$cdoc->title));
      $doc=createDoc($dbaccess,$classid); // the doc inherit from chosen class
      if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
      
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
  
  $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid);
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
  
  
  
  //$frames= $query->Query(0,0,"TABLE","select distinct frametext from DocAttr" );
  $frames=array();
  $listattr = $doc->GetAttributes();
  
  
  
  $nattr = count($listattr); // number of attributes
    
    
  $k=0; // number of frametext
  $v=0;// number of value in one frametext
  $currentFrameId="";
  $changeframe=false;
  $ih = 0; // index for hidden values
  $thidden =array();
  $tableframe=array();

  $iattr=0;
  while (list($i,$attr) = each($listattr)) {
    if ($attr->visibility == "M") continue;
    $iattr++;
    
    // Compute value elements
	    
    if ($docid > 0) $value = $doc->GetValue($listattr[$i]->id);
    else $value = $cdoc->GetValue($listattr[$i]->id);
	    	    

    if ( $currentFrameId != $listattr[$i]->frameid) {
      if ($currentFrameId != "") $changeframe=true;
    }
	    
      
      
      
    if ( $changeframe){  // to generate final frametext
	      
      $changeframe=false;
      if ($v > 0 ) {// one value detected	  
	      
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
    if ( ($listattr[$i]->type != "frame"))
      {
	      
	$currentFrameId = $listattr[$i]->frameid;
	if ( ($listattr[$i]->visibility == "H") || 
	     ($listattr[$i]->visibility == "R") && (substr_count($listattr[$i]->type,"text") > 0)) {
	  // special case for hidden values
	  $thidden[$ih]["hname"]= "_".$listattr[$i]->id;
	  $thidden[$ih]["hid"]= $listattr[$i]->id;
	  if ($value == "") $thidden[$ih]["hvalue"] = GetHttpVars($listattr[$i]->id);
	  else $thidden[$ih]["hvalue"]=chop(htmlentities($value));
	  
	  
	  $thidden[$ih]["inputtype"]=getHtmlInput($action, 
						  $doc->id,
						  $listattr[$i],
						  $value);
	  $ih++;

	} else {
	  $tableframe[$v]["value"]=chop(htmlentities($value));
	  $label = $listattr[$i]->labeltext;
	  $tableframe[$v]["attrid"]=$listattr[$i]->id;
	  $tableframe[$v]["name"]=chop("[TEXT:".$label."]");

	  if ($listattr[$i]->visibility == "N") $tableframe[$v]["labelclass"]="FREEDOMLabelNeeded";
	  else $tableframe[$v]["labelclass"]="FREEDOMLabel";

	  //$tableframe[$v]["name"]=$action->text($label);
	  $tableframe[$v]["inputtype"]=getHtmlInput($action, 
						    $doc->id,
						    $listattr[$i],
						    $value);
		
		
		
		
	  $v++;
		
	}
      }
  }
  
  // Out
  if ($v > 0 ) {// latest fieldset
	  
	      
    $frames[$k]["frametext"]="[TEXT:".$doc->GetLabel($currentFrameId)."]";
    $frames[$k]["TABLEVALUE"]="TABLEVALUE_$k";
    $action->lay->SetBlockData($frames[$k]["TABLEVALUE"],
			       $tableframe);
	    
  }
    
  $action->lay->SetBlockData("HIDDENS",$thidden);
  $action->lay->SetBlockData("TABLEBODY",$frames);
  
  

      
  
  
}
?>
