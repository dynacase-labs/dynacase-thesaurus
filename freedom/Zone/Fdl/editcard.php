<?php

// ---------------------------------------------------------------
// $Id: editcard.php,v 1.27 2003/05/23 15:30:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/editcard.php,v $
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

// -----------------------------------
function editcard(&$action) {
  // -----------------------------------
  
  $docid = GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $zonebodycard = GetHttpVars("zone"); // define view action
  $usefordef = GetHttpVars("usefordef"); // default values for a document


  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");

  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/datepicker.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  

 
 
    
  if ($docid == 0) { // new document
    if ($classid > 0) {
      $doc= createDoc($dbaccess,$classid);
      if ($zonebodycard == "") $zonebodycard=$doc->defaultedit;
    }
  } else { // modify document
    
      $doc= new Doc($dbaccess,$docid);
      if ($zonebodycard == "") $zonebodycard=$doc->defaultedit;
    
  }
  if ($zonebodycard == "") $zonebodycard="FDL:EDITBODYCARD";

  if ($usefordef=="Y") {
    $zonebodycard="FDL:EDITBODYCARD"; // always default view for default document
    $fdoc = new DocFam($dbaccess, $classid);
    $doc->usefor='D';
    $doc->setDefaultValues($fdoc->defval);    
  }

  $action->lay->Set("classid", $classid);
  $action->lay->Set("usefordef", $usefordef);
  $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard));

  // compute modify condition js
  $attrn = $doc->GetNeededAttributes();
  
  if (count($attrn) == 0) $sattrNid = "[]";
  else {
    while(list($k,$v) = each($attrn)) {
      $attrNid[]=$v->id;
    }
    $sattrNid = "['".implode("','",$attrNid)."']";
  }



  //compute constraint for enable/disable input
  $doc->Refresh();
    

  $ka=0;
  $tjsa=array();

    reset($doc->paramRefresh);
  
    while(list($k,$v) = each($doc->paramRefresh)) {

      $tjsa[]=array("jstain" => "['".implode("','", $v["in"])."']",
		    "jstaout" => "['".implode("','", $v["out"])."']",
		    "jska"=> "$ka");
      $ka++;
	
      
    
    }
  

  // contruct js functions
  $jsfile=$action->GetLayoutFile("editcard.js");
  $jslay = new Layout($jsfile,$action);
  $jslay->Set("attrnid",$sattrNid);
  $jslay->SetBlockData("RATTR",$tjsa);
  $action->parent->AddJsCode($jslay->gen());
}

function moreone($v) {
  return (strlen($v) > 1);
}
?>
