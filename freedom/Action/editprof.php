<?php
// ---------------------------------------------------------------
// $Id: editprof.php,v 1.4 2001/11/30 15:13:39 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/editprof.php,v $
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
// $Log: editprof.php,v $
// Revision 1.4  2001/11/30 15:13:39  eric
// modif pour Css
//
// Revision 1.3  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.1  2001/11/09 09:41:13  eric
// gestion documentaire
//

// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");

function editprof(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $createp = GetHttpVars("create",0); // 1 if use for create profile (only for familly)

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  $action->lay->Set("docid",$docid);
  $action->lay->Set("create",$createp);

  if ($createp) $action->lay->Set("TITLE",_("change creation profile"));
  else  $action->lay->Set("TITLE",_("change profile"));


  $doc= new Doc($dbaccess,$docid);
  // build values type array
  $odocattr= new DocAttr($dbaccess);

  $action->lay->Set("doctitle",_("new profile document"));



  $selectclass=array();
  $tclassdoc = $doc->GetProfileDoc();
  if (is_array($tclassdoc)) {
  while (list($k,$pdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idpdoc"]=$pdoc->id;
    $selectclass[$k]["profname"]=$pdoc->title;
    $selectclass[$k]["selected"]="";
  }
  }


  $selectframe= array();
  $selectoption= array();
  while (list($k,$type)= each ($odocattr->deftype)) {
    $selectoption[$k]["typevalue"]=$type;
    $selectoption[$k]["selected"]="";
  }

  $nbattr=0; // if new document 

  // display current values
  $newelem=array();
  if ($docid > 0) {


    $doc->GetFathersDoc();
    $action->lay->Set("doctitle",$doc->title);

    if ($doc->profid == -1) 
      $action->lay->Set("selected_spec","selected");
    else {
      $action->lay->Set("selected_spec","");
      // selected the current class document

      if ($createp) $sprofid = $doc->cprofid;
      else $sprofid = $doc->profid;
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
