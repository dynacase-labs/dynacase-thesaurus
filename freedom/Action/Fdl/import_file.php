<?php
// ---------------------------------------------------------------
// $Id: import_file.php,v 1.33 2002/12/06 17:15:15 eric Exp $
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

include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.DocSearch.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/Lib.Attr.php");

function add_import_file(&$action, $fimport="") {
  // -----------------------------------
  global $HTTP_POST_FILES;
  $gerr=""; // general errors

  ini_set("max_execution_time", 300);
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc 
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if (isset($HTTP_POST_FILES["file"]))    
    {
      $fdoc = fopen($HTTP_POST_FILES["file"]['tmp_name'],"r");
    } else {
      if ($fimport != "")      $fdoc = fopen($fimport,"r");
      else $fdoc = fopen(GetHttpVars("file"),"r");
    }

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
      $doc=new DocFam($dbaccess, $data[3]);

      if ($analyze) continue;
      if (! $doc->isAffected())  {
	//$doc = createDoc($dbaccess, $data[1]);
	$doc  =new DocFam($dbaccess);
	if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
	if (isset($data[3]) && ($data[3] > 0)) $doc->id= $data[3]; // static id
	$err = $doc->Add();
      }
      $doc->fromid = $data[1];

      $doc->title =  $data[2];  
     
      
      if (isset($data[4])) $doc->classname = $data[4]; // new classname for familly

      if (isset($data[5])) $doc->name = $data[5]; // internal name



    if ($err != "") $gerr="\nline $nline:".$err;

	  
    break;
    // -----------------------------------
    case "END":

      
      if ($analyze) continue;
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

      
      if (  $doc->doctype=="C") {

	createDocFile($dbaccess, get_object_vars($doc)); 
	$msg=PgUpdateFamilly($dbaccess, $doc->id);
      }
      
    
    break;
    // -----------------------------------
    case "DOC":
      $ndoc=csvAddDoc($action,$dbaccess, $data, $dirid);
      
    break;    
    // -----------------------------------
    case "SEARCH":
    $doc = createDoc($dbaccess,5);

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
    case "DFLDID":
      $doc->dfldid =  $data[1];
    break;
    // -----------------------------------
    case "WID":
      $doc->wid =  $data[1];
    break;
    // -----------------------------------
    case "METHOD":
      $doc->methods =  $data[1];
    break;
    // -----------------------------------
    case "USEFORPROF":     
      $doc->usefor =  "P";
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
      if     ($num < 13) print "Error in line $nline: $num cols < 14<BR>";
      $oattr=new DocAttr($dbaccess, array($doc->id,strtolower($data[1])));
     
      
      $oattr->docid = $doc->id;
      $oattr->id = strtolower($data[1]);
      $oattr->frameid = strtolower($data[2]);
      $oattr->labeltext=$data[3];

      if (! $oattr->isAffected()) { 
	// don't change config by admin
	$oattr->title = ($data[4] == "Y")?"Y":"N";
	$oattr->abstract = ($data[5] == "Y")?"Y":"N";
      }
      $oattr->type = $data[6];

      $oattr->ordered = $data[7];
      $oattr->visibility = ($oattr->type=="frame")?"F":$data[8];
      $oattr->needed =  ($data[9]=="Y")?"Y":"N";
      $oattr->link = $data[10];
      $oattr->phpfile = $data[11];
      $oattr->phpfunc = $data[12];
      if (isset($data[13])) $oattr->elink = $data[13];
	  
      if ($oattr->isAffected()) $err =$oattr ->Modify();
      else    $err = $oattr ->Add();
      //    if ($err != "") $err = $oattr ->Modify();
    if ($err != "") $gerr="\nline $nline:".$err;
    break;
	  
    }

	  
  }
      
  fclose ($fdoc);


  if (isset($HTTP_POST_FILES["file"])) {
    if ($gerr != "") $action->exitError($gerr);

  } else {
    print $gerr;
  }
    
  
}

function csvAddDoc(&$action,$dbaccess, $data, $dirid=10) {
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze
  $wdouble = (GetHttpVars("double","N")=="Y"); // with double title document

  // like : DOC;120;...
  $err="";
  $doc = createDoc($dbaccess, $data[1]);
  if (! $doc) return;

  $msg =""; // information message
  $doc->fromid = $data[1];
  if  ($data[2] > 0) $doc->id= $data[2]; // static id
  if ( (intval($doc->id) == 0) || (! $doc -> Select($doc->id))) {
    if (! $analyze) {
      $err = $doc->Add();
      $msg .= $err . sprintf(_("add id [%d] "),$doc->id); 
    } else {
      $msg .=  sprintf(_("add [%s] "),implode('-',$data)); 
    }
  } else {
    $msg .= $err . sprintf(_("update id [%d] "),$doc->id);
    
  }
  
    
  if ($err != "") {
    global $nline, $gerr;
    $gerr="\nline $nline:".$err;
    return false;
  }
  $lattr = $doc->GetImportAttributes();



  $iattr = 4; // begin in 5th column
  reset($lattr);
  while (list($k, $attr) = each ($lattr)) {

    if (isset($data[$iattr]) &&  ($data[$iattr] != "")) {
      $doc->setValue($attr->id, "${data[$iattr]}");
    }
    $iattr++;
  }
  if (! $analyze) {
    // update title in finish
    $doc->refresh(); // compute read attribute
    if (! $wdouble) {
      // test if same doc in database
      $doc->RefreshTitle();
      $lsdoc = $doc->GetDocWithSameTitle();

      if (count($lsdoc) > 0) {
	$msg .= $err.sprintf(_("double title %s <B>ignored</B> "),$doc->title); 
	$doc->delete(true); // no post delete it's not a really doc yet
	$doc=false; 
      } else {
	// no double title found
	$doc->modify();
	$msg .= $doc->postModify(); // case special classes
      }
    } else {
      // with double title
      $doc->modify();
      $msg .= $doc->postModify(); // case special classes
    }
  }

  if ($doc) {

    $msg .= $doc->title;
    if ($data[3] > 0) { // dirid
      $dir = new Doc($dbaccess, $data[3]);
      if (! $analyze) $dir->AddFile($doc->id);
      $msg .= $err.sprintf(_("and add in %s folder "),$dir->title); 
    } else if ($data[3] ==  0) {
      if ($dirid > 0) {
	$dir = new Dir($dbaccess, $dirid);
	if (! $analyze) $dir->AddFile($doc->id);
	$msg .= $err.sprintf(_("and add  in %s folder "),$dir->title); 
      }
    }
  }
  if (isset($action->lay)) {
    $tmsg = $action->lay->GetBlockData("MSG");
    $tmsg[] = array("msg"=>$msg);
    $tmsg = $action->lay->SetBlockData("MSG",$tmsg);
  } else {
    print $msg."\n";
  }


  return $doc;
}

?>
