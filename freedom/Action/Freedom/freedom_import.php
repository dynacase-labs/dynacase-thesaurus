<?php
// ---------------------------------------------------------------
// $Id: freedom_import.php,v 1.2 2002/06/19 12:32:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_import.php,v $
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

// ---------------------------------------------------------------
include_once("FDL/import_file.php");





// -----------------------------------
function freedom_import(&$action) {
  // -----------------------------------

  // Get all the params   
  $classid = GetHttpVars("classid",0); // doc familly
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc (default unclassed folder)

  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  // build list of class document
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");

  $selectclass=array();
  if ($classid == 0) $classid=$tclassdoc[0]->initid;

  $doc = new Doc($dbaccess, $classid);
  $tclassdoc = $doc->GetClassesDoc($classid);

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
    if ($cdoc->initid == $classid) $selectclass[$k]["selected"]="selected";
    else $selectclass[$k]["selected"]="";
  }


  $action->lay->SetBlockData("SELECTCLASS", $selectclass);


  $lattr = $doc->GetAttributes();
  $format = "DOC;".$doc->id.";<special id>;<special dirid>; ";

  while (list($k, $attr) = each ($lattr)) {
    $format .= $attr->labeltext." ;";
  }



  $action->lay->Set("dirid",$dirid);
  $action->lay->Set("rows",count($lattr)+2);
  $action->lay->Set("format",$format);
}



?>
