<?php
/**
 * generate interface for the rdition of document
 *
 * @author Anakeen 2003
 * @version $Id: editcard.php,v 1.35 2003/12/30 10:12:57 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: editcard.php,v 1.35 2003/12/30 10:12:57 eric Exp $
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
  $vid = GetHttpVars("vid"); // special controlled view


  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/DHTMLapi.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/idoc.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/datepicker.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  

  if (($usefordef=="Y") && ($zonebodycard == "")) $zonebodycard="FDL:EDITBODYCARD";// always default view for default document
 
    
  if ($docid == 0) { // new document
    if ($classid > 0) {
      $doc= createDoc($dbaccess,$classid);
      if ($zonebodycard == "") $zonebodycard=$doc->defaultedit;
    }
  } else { // modify document
    
      $doc= new Doc($dbaccess,$docid);
      if ($zonebodycard == "") $zonebodycard=$doc->defaultedit;
    
  }

  if ($usefordef=="Y") {
    
    $fdoc = new DocFam($dbaccess, $classid);
    $doc->usefor='D';
    $doc->setDefaultValues($fdoc->defval);    
  }

  if (($vid != "") && ($doc->cvid > 0)) {
    // special controlled view
    $cvdoc= new Doc($dbaccess, $doc->cvid);
    $err = $cvdoc->control($vid); // control special view
    if ($err != "") $action->exitError($err);
    $tview = $cvdoc->getView($vid);
    $doc->setMask($tview["CV_MSKID"]);
    if ($zonebodycard == "") $zonebodycard=$tview["CV_ZVIEW"];
  }

  if (GetHttpVars("viewconstraint")=="Y") { // from modcard function if constraint error
    
    include_once("FDL/modcard.php");  
    setPostVars($doc); // HTTP VARS comes from previous edition

    
  }

  if ($zonebodycard == "") $zonebodycard="FDL:EDITBODYCARD";
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
  $tjsa=array();
  if ($usefordef != "Y") {
    if (GetHttpVars("viewconstraint")!="Y") $doc->Refresh();
    else {
      $err=$doc->SpecRefresh();
      $err.=$doc->SpecRefreshGen();      
    }
    
    $ka=0;

    reset($doc->paramRefresh);
  
    while(list($k,$v) = each($doc->paramRefresh)) {

      $tjsa[]=array("jstain" => "['".implode("','", $v["in"])."']",
		    "jstaout" => "['".implode("','", $v["out"])."']",
		    "jska"=> "$ka");
      $ka++;
	
          
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
