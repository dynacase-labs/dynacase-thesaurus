<?php
// ---------------------------------------------------------------
// $Id: editprof.php,v 1.7 2002/10/08 10:27:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editprof.php,v $
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

function editprof(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $createp = GetHttpVars("create",0); // 1 if use for create profile (only for familly)


  $action->lay->Set("docid",$docid);
  $action->lay->Set("create",$createp);

  if ($createp) $action->lay->Set("TITLE",_("change creation profile"));
  else  $action->lay->Set("TITLE",_("change profile"));


  $doc= new Doc($dbaccess,$docid);
  // build values type array
 

  $action->lay->Set("doctitle",_("new profile document"));



  $selectclass=array();
  if ($doc->useforprof != "t") { // cannot redirect profil document (only normal document)
    $tclassdoc = $doc->GetProfileDoc();
    if (is_array($tclassdoc)) {
      while (list($k,$pdoc)= each ($tclassdoc)) {
	if ($pdoc->id != $doc->id) {
	  $selectclass[$k]["idpdoc"]=$pdoc->id;
	  $selectclass[$k]["profname"]=$pdoc->title;
	  $selectclass[$k]["selected"]="";
	}
      }
    }
  }


  $nbattr=0; // if new document 

  // display current values
  $newelem=array();
  if ($docid > 0) {


    $doc->GetFathersDoc();
    $action->lay->Set("doctitle",$doc->title);

    if ($createp) $sprofid = $doc->cprofid;
    else $sprofid = $doc->profid;

    if ($sprofid == $doc->id) 
      $action->lay->Set("selected_spec","selected");
    else {
      $action->lay->Set("selected_spec","");
      // selected the current class document

      
      while (list($k,$pdoc)= each ($selectclass)) {
	//      print $doc->doctype." == ".$selectclass[$k]["idcdoc"]."<BR>";
	if ($sprofid == $selectclass[$k]["idpdoc"]) {
	  $selectclass[$k]["selected"]="selected";
	}
      }
    }
  
    $action->lay->SetBlockData("SELECTPROF", $selectclass);
	  
      
    
  }


}

?>
