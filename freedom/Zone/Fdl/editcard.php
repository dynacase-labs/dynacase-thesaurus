<?php

// ---------------------------------------------------------------
// $Id: editcard.php,v 1.15 2002/09/02 16:32:25 eric Exp $
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


  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/datepicker.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  

 

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  if ($zonebodycard == "") {
    
    if ($docid == 0) { // new document
      if ($classid > 0) {
	$doc= new Doc($dbaccess,$classid);
	$zonebodycard=$doc->deditzone;
      }
    } else { // modify document
      if ($classid == 0) {
	$doc= new Doc($dbaccess,$docid);
	$zonebodycard=$doc->deditzone;
      } else {
	$doc= new Doc($dbaccess,$classid);
	$zonebodycard=$doc->deditzone;
      }
    }
  }
  if ($zonebodycard == "") $zonebodycard="FDL:EDITBODYCARD";


  $action->lay->Set("classid", $classid);
  $action->lay->Set("ZONEBODYCARD", $zonebodycard);


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
    $rattr = $doc->GetComputedAttributes();

  $ka=0;
  $tjsa=array();
    while(list($k,$v) = each($rattr)) {
      
      if (ereg("\(([^\)]+)\):(.+)", $v->phpfunc, $reg)) {
	$ain = array_filter(explode(",",$reg[1]),"moreone");
	if (count($ain) > 0) {
	  
	  $aout = explode(",",$reg[2]);
	  $tjsa[]=array("jstain" => "['".implode("','", $ain)."']",
			"jstaout" => "['".implode("','", $aout)."']",
			"jska"=> "$ka");
	  $ka++;
	}
      }
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
