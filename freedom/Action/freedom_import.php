<?php
// ---------------------------------------------------------------
// $Id: freedom_import.php,v 1.4 2001/12/08 17:16:30 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_import.php,v $
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
// $Log: freedom_import.php,v $
// Revision 1.4  2001/12/08 17:16:30  eric
// evolution des attributs
//
// Revision 1.3  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.3  2001/09/10 16:51:45  eric
// ajout accessibilté objet
//
// Revision 1.2  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.1  2001/06/19 16:13:20  eric
// importation de fichier
//
//
// ---------------------------------------------------------------
include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.QueryDir.php");





// -----------------------------------
function freedom_import(&$action) {
  // -----------------------------------

  // Get all the params   
  $classid = GetHttpVars("classid",0); // doc familly
  $dirid = GetHttpVars("dirid",0); // directory to place imported doc 

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
  $format = "BEGIN:".$doc->id.":[".chop($doc->title)."]\n";

  while (list($k, $attr) = each ($lattr)) {
    $format .= $attr->id.":[".$attr->labeltext."]:<"._("value").">\n";
  }
  $format .= "END:$doc->id\n";


  $action->lay->Set("dirid",$dirid);
  $action->lay->Set("rows",count($lattr)+2);
  $action->lay->Set("format",$format);
}

function add_import_file(&$action, $fimport="") {
  // -----------------------------------
  global $HTTP_POST_FILES;

  $dirid = GetHttpVars("dirid",0); // directory to place imported doc 

  $dbaccess = $action->GetParam("FREEDOM_DB");


  $action->lay->Set("CR","");
  if (isset($HTTP_POST_FILES["tsvfile"]))    
    {
      $fdoc = fopen($HTTP_POST_FILES["tsvfile"]['tmp_name'],"r");
    } else $fdoc = fopen($fimport,"r");

  if (! $fdoc) $action->exitError(_("no import file specified"));
  while ($data = fgetcsv ($fdoc, 1000, ":")) {
    $num = count ($data);
    if ($num < 2) continue;

    switch ($data[0]) {
      // -----------------------------------
    case "BEGIN":
      $doc = new Doc($dbaccess);
    $doc->fromid = $data[1];

    if ($data[2] > 0) $doc->id= $data[2]; // static id

    $err = $doc->Add();

    if ($err != "") $action->exitError($err);
    $bdvalue = new DocValue($dbaccess);
    $bdvalue->docid = $doc->id;
	  
    break;
    // -----------------------------------
    case "END":
      if ($num > 3) $doc->doctype = "S";
      $doc->title =  GetTitle($dbaccess,$doc->id);
      $doc->modify();
      $qf = new QueryDir($dbaccess);
      if (($num < 3) || ($data[2] == 0)) $qf->dirid=$dirid; // current folder
      else $qf->dirid=$data[2]; // specific folder

      $qf->query="select id from doc where id=".$doc->id;
      $qf->qtype='S'; // single user query
      $err = $qf->Add();
      if ($err != "") $action->exitError($err);

      
      if ($num > 3) { // specific search 
	$qf->qid="";
	$qf->dirid=$doc->id;
	$qf->qtype='M'; // complex query
	$qf->query=$data[3];
	$err = $qf->Add();
	if ($err != "") $action->exitError($err);
      }
    
    break;
    // -----------------------------------
    case "TYPE":
      $doc->doctype =  $data[1];
    break;
    // -----------------------------------
    case "ATTR":
	    
      $oattr=new DocAttr($dbaccess);
    $oattr->docid = $doc->id;
    $oattr->id = $data[1];
    $oattr->frameid = $data[2];
    $oattr->labeltext=$data[3];
    $oattr->title = ($data[4] == "Y")?"Y":"N";
    $oattr->abstract = ($data[5] == "Y")?"Y":"N";
    $oattr->type = $data[6];
    $oattr->ldapname = $data[7];
    $oattr->ordered = $data[8];
	  
    $err = $oattr ->Add();
    if ($err != "") $action->exitError($err);
    break;
    // -----------------------------------
    case ($data[0] > 0):
      $bdvalue->attrid = $data[0];
    $bdvalue->value = $data[2];
    $bdvalue ->Add();
    break;
	  
    }

	  
  }
      
  fclose ($fdoc);


  if (isset($HTTP_POST_FILES["tsvfile"]))  
    redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");

    
  
}


?>
