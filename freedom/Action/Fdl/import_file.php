<?php
// ---------------------------------------------------------------
// $Id: import_file.php,v 1.22 2002/09/25 08:36:06 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/import_file.php,v $
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
include_once("FDL/Class.DocSearch.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");

function add_import_file(&$action, $fimport="") {
  // -----------------------------------
  global $HTTP_POST_FILES;
  $gerr=""; // general errors

  ini_set("max_execution_time", 300);
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc 


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (isset($HTTP_POST_FILES["tsvfile"]))    
    {
      $fdoc = fopen($HTTP_POST_FILES["tsvfile"]['tmp_name'],"r");
    } else $fdoc = fopen($fimport,"r");

  if (! $fdoc) $action->exitError(_("no import file specified"));
  $nline=0;
  while ($data = fgetcsv ($fdoc, 2000, ";")) {
    $nline++;
    $num = count ($data);
    if ($num < 1) continue;
    switch ($data[0]) {
      // -----------------------------------
    case "BEGIN":
      $err="";
      $doc=new Doc($dbaccess, $data[3]);
      if (! $doc->isAffected())  {
	$doc = createDoc($dbaccess, $data[1]);
	if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));

	$doc->fromid = $data[1];

	$doc->title =  $data[2];  
	if (isset($data[3]) && ($data[3] > 0)) $doc->id= $data[3]; // static id
	  if (isset($data[4]) && ($data[3] != "")) $doc->classname = $data[4]; // new classname for familly

	    $err = $doc->Add();
      }


    if ($err != "") $gerr="\nline $nline:".$err;

	  
    break;
    // -----------------------------------
    case "END":

      
      $action->log->debug("add ");
      if (($num > 3) && ($data[3] != "")) $doc->doctype = "S";
      $doc->modify();

      if (isset($data[2])) {
	if  ($data[2] > 0) { // dirid
	  $dir = new Dir($dbaccess, $data[2]);
	  $dir->AddFile($doc->id);
	} else if ($data[2] ==  0) {
	  $dir = new Dir($dbaccess, $dirid);
	  $dir->AddFile($doc->id);
	}
      }

      
      
      
    
    break;
    // -----------------------------------
    case "DOC":
      $ndoc=csvAddDoc($dbaccess, $data, $dirid);
      if (! isset($HTTP_POST_FILES["tsvfile"])) {
	// log of batch mode
	print $ndoc->title."\n";
      }
    break;    
    // -----------------------------------
    case "SEARCH":
    $doc = new DocSearch($dbaccess);

    if  ($data[1] > 0) $doc->id= $data[1]; // static id
    $err = $doc->Add();
    if (($err != "") && ($doc->id > 0)) { // case only modify
      if ($doc -> Select($doc->id)) $err = "";
    }
    if ($err != "") $gerr="\nline $nline:".$err;
    
    // update title in finish
    $doc->title =  $data[3];
    $doc->modify();

    if (($data[4] != "")) { // specific search 
	$err = $doc->AddQuery($data[4]);
	if ($err != "") $gerr="\nline $nline:".$err;
      }

    if ($data[2] > 0) { // dirid
      $dir = new Dir($dbaccess, $data[2]);
      $dir->AddFile($doc->id);
    } else if ($data[2] ==  0) {
      $dir = new Dir($dbaccess, $dirid);
      $dir->AddFile($doc->id);
    }
    break;
    // -----------------------------------
    case "TYPE":
      $doc->doctype =  $data[1];
    break;
    // -----------------------------------
    case "DVIEWZONE":
      $doc->dviewzone =  $data[1];
    break;
    // -----------------------------------
    case "DFLDID":
      $doc->dfldid =  $data[1];
    break;
    // -----------------------------------
    case "WID":
      $doc->wid =  $data[1];
    break;
    // -----------------------------------
    case "DEDITZONE":
      $doc->deditzone =  $data[1];
    break;
    // -----------------------------------
    case "USEFORPROF":     
      $doc->useforprof =  "t";
    break;
    // -----------------------------------
    case "CPROFID":     
      $doc->cprofid =  $data[1];
    break;
    // -----------------------------------
    case "PROFID":     
      $doc->profid =  $data[1];
    break;
    // -----------------------------------
    case "ATTR":
      if     ($num < 12) print "Error in line $nline: $num cols < 13<BR>";
      $oattr=new DocAttr($dbaccess, array($doc->id,$data[1]));
      if ($oattr->isAffected()) $amodify=true;
      else $amodify=false;
      
      $oattr->docid = $doc->id;
      $oattr->id = $data[1];
      $oattr->frameid = $data[2];
      $oattr->labeltext=$data[3];
      $oattr->title = ($data[4] == "Y")?"Y":"N";
      $oattr->abstract = ($data[5] == "Y")?"Y":"N";
      $oattr->type = $data[6];

      $oattr->ordered = $data[7];
      $oattr->visibility = $data[8];
      $oattr->link = $data[9];
      $oattr->phpfile = $data[10];
      $oattr->phpfunc = $data[11];
      if (isset($data[12])) $oattr->elink = $data[12];
	  
      if ($oattr->isAffected()) $err =$oattr ->Modify();
      else    $err = $oattr ->Add();
      //    if ($err != "") $err = $oattr ->Modify();
    if ($err != "") $gerr="\nline $nline:".$err;
    break;
	  
    }

	  
  }
      
  fclose ($fdoc);
  if ($gerr != "") $action->exitError($gerr);


  if (isset($HTTP_POST_FILES["tsvfile"]))  
    redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");

    
  
}

function csvAddDoc($dbaccess, $data, $dirid=10) {
  // like : DOC;120;...
  $err="";
  $doc = createDoc($dbaccess, $data[1]);
  $doc->fromid = $data[1];
  if  ($data[2] > 0) $doc->id= $data[2]; // static id
  if ( (intval($doc->id) == 0) || (! $doc -> Select($doc->id))) $err = $doc->Add();
    
  if ($err != "") {
    global $nline, $gerr;
    $gerr="\nline $nline:".$err;
    return false;
  }
  $lattr = $doc->GetNormalAttributes();



  $iattr = 4; // begin in 5th column
  reset($lattr);
  while (list($k, $attr) = each ($lattr)) {

    if (isset($data[$iattr]) &&  ($data[$iattr] != "")) {
      $doc->setValue($attr->id, $data[$iattr]);
    }
    $iattr++;
  }
  // update title in finish
  $doc->refresh(); // compute read attribute
  $doc->modify();
  $doc->postModify(); // case special classes
  if ($data[3] > 0) { // dirid
    $dir = new Dir($dbaccess, $data[3]);
    $dir->AddFile($doc->id);
  } else if ($data[3] ==  0) {
    $dir = new Dir($dbaccess, $dirid);
    $dir->AddFile($doc->id);
  }

  return $doc;
}

?>
