<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: editprof.php,v 1.15 2004/05/06 08:04:40 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: editprof.php,v 1.15 2004/05/06 08:04:40 eric Exp $
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
include_once("FDL/Lib.Dir.php");

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

  // control view acl
  $err= $doc->Control("viewacl");
  if ($err != "")    $action-> ExitError($err);
  

  $action->lay->Set("doctitle",_("new profile document"));



  $selectclass=array();
  if ($doc->usefor != "P") { // cannot redirect profil document (only normal document)
    if ($createp) {
      // search from profil of the document family (not the family)
      $tdoc= createDoc($dbaccess, $doc->id);
      $tclassdoc = GetProfileDoc($dbaccess,$doc->id,$tdoc->defProfFamId);
    } else $tclassdoc = GetProfileDoc($dbaccess,$doc->id);
    if (is_array($tclassdoc)) {
      while (list($k,$pdoc)= each ($tclassdoc)) {
	if ($pdoc["id"] != $doc->id) {
	  $selectclass[$k]["idpdoc"]=$pdoc["id"];
	  $selectclass[$k]["profname"]=$pdoc["title"];
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

    if ($createp) $sprofid = abs($doc->cprofid);
    else {
      $sprofid = abs($doc->profid);
      // select dynamic profil if set
      if ($doc->dprofid != 0)  $sprofid = abs($doc->dprofid);
    }

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

  setControlView($action,$doc,$createp);

}


function setControlView(&$action,&$doc,$createp=false) {

  $filter=array();
  $chdoc=$doc->GetFromDoc();

  $filter[]=GetSqlCond($chdoc,"cv_famid");
//   if ($doc->doctype=='C') $filter[]="cv_famid=".$doc->id;
//   else $filter[]="cv_famid=".$doc->fromid;
  $tcv = getChildDoc($doc->dbaccess,
		      0,0,100,$filter,$action->user->id,"TABLE","CVDOC");
  
  foreach ($tcv as $k=>$v) {
    
    $tcv[$k]["selcv"]="";
    if ($createp) if ($v["id"]==$doc->ccvid) $tcv[$k]["selcv"]="selected";
    else if ($v["id"]==$doc->cvid) $tcv[$k]["selcv"]="selected";


  }
  $action->lay->SetBlockData("SELECTCV", $tcv);

}

?>
