<?php
// ---------------------------------------------------------------
// $Id: defattr.php,v 1.14 2003/04/25 14:51:31 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/defattr.php,v $
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

include_once("FDL/Lib.Dir.php");
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

function defattr(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $classid = GetHttpVars("classid",0); // use when new doc or change class
  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc


  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");


  $action->lay->Set("docid",$docid);
  $action->lay->Set("dirid",$dirid);

  $doc= new Doc($dbaccess,$docid);
  // build values type array
  $odocattr= new DocAttr($dbaccess);

  $action->lay->Set("TITLE",_("new document family"));


  // when modification 
  if (($classid == 0) && ($docid != 0) ) $classid=$doc->fromid;
  else
    // to show inherit attributes
    if (($docid == 0) && ($classid > 0)) $doc=new Doc($dbaccess,$classid); // the doc inherit from chosen class

  $selectclass=array();
  $tclassdoc = GetClassesDoc($dbaccess, $action->user->id,$classid);
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->id;
    $selectclass[$k]["classname"]=$cdoc->title;
    $selectclass[$k]["selected"]="";
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

    // control if user can update 
    $err = $doc->CanLockFile();
    if ($err != "")   $action->ExitError($err);
    $action->lay->Set("TITLE",$doc->title);
  }
  if (($classid > 0) || ($doc->doctype = 'C')) {
   

    // selected the current class document
    while (list($k,$cdoc)= each ($selectclass)) {

      if ($classid == $selectclass[$k]["idcdoc"]) {

	$selectclass[$k]["selected"]="selected";
      }
    }
    

    $ka = 0; // index attribute

    //    ------------------------------------------
    //  -------------------- FIELDSET ----------------------
    $tattr = $doc->GetFieldAttributes();
   
    $selectframe= array();
    reset($tattr);
    while (list($k,$attr)= each ($tattr)) {
      if ($attr->docid > 0) {
	$selectframe[$k]["framevalue"]=$attr->labelText;
	$selectframe[$k]["frameid"]=$attr->id;
	$selectframe[$k]["selected"]="";
	$newelem[$k]["attrid"]=$attr->id;
	$newelem[$k]["attrname"]=$attr->labelText;
	$newelem[$k]["neweltid"]=$k;
	$newelem[$k]["visibility"]="F";
	$newelem[$k]["typevalue"]="frame";
	$newelem[$k]["disabledid"]="disabled";
	$newelem[$k]["order"]="0";
	$newelem[$k]["SELECTFRAME"]="SELECTFRAME_$k";
	if ($attr->docid == $docid) {
	  $newelem[$k]["disabled"]="";
	} else {
	  $newelem[$k]["disabled"]="disabled";
	}

	// unused be necessary for layout
      $newelem[$k]["link"]="";
      $newelem[$k]["phpfile"]="";
      $newelem[$k]["phpfunc"]="";
      $newelem[$k]["elink"]="";
      $newelem[$k]["abscheck"]="";
      $newelem[$k]["neededcheck"]="";
      $newelem[$k]["titcheck"]="";
      }	  
      $ka++;
    }

    //    ------------------------------------------
    //  -------------------- MENU ----------------------
    $tattr = $doc->GetMenuAttributes();
   
    reset($tattr);
    while (list($k,$attr)= each ($tattr)) {
      if ($attr->docid > 0) {
	$newelem[$k]["attrid"]=$attr->id;
	$newelem[$k]["attrname"]=$attr->labelText;
	$newelem[$k]["neweltid"]=$k;
	$newelem[$k]["visibility"]="M";
	$newelem[$k]["typevalue"]="";
	$newelem[$k]["order"]=$attr->ordered;
	$newelem[$k]["disabledid"]="disabled";
	$newelem[$k]["SELECTFRAME"]="SELECTFRAME_$k";
	if ($attr->docid == $docid) {
	  $newelem[$k]["disabled"]="";
	} else {
	  $newelem[$k]["disabled"]="disabled";
	}

      $newelem[$k]["link"]=$attr->link;
	// unused be necessary for layout
      $newelem[$k]["phpfile"]="";
      $newelem[$k]["phpfunc"]="";
      $newelem[$k]["elink"]="";
      $newelem[$k]["abscheck"]="";
      $newelem[$k]["titcheck"]="";
      }	  
      $ka++;
    }

    //    ------------------------------------------
    //  -------------------- NORMAL ----------------------
    $tattr = $doc->GetNormalAttributes();

    uasort($tattr,"tordered"); 
    reset($tattr);
    while(list($k,$attr) = each($tattr))  {
      if ($attr->type=="array") {
	$selectframe[$k]["framevalue"]=$attr->labelText;
	$selectframe[$k]["frameid"]=$attr->id;
	$selectframe[$k]["selected"]="";
      }
      $newelem[$k]["attrid"]=$attr->id;
      $newelem[$k]["attrname"]=$attr->labelText;
      $newelem[$k]["order"]=$attr->ordered;
      $newelem[$k]["visibility"]=$attr->visibility;
      $newelem[$k]["link"]=$attr->link;
      $newelem[$k]["phpfile"]=$attr->phpfile;
      $newelem[$k]["phpfunc"]=$attr->phpfunc;
      $newelem[$k]["elink"]=$attr->elink;
      $newelem[$k]["disabledid"]="disabled";
      $newelem[$k]["neweltid"]=$k;
      if ($attr->isInAbstract) {
	$newelem[$k]["abscheck"]="checked";
      } else {
	$newelem[$k]["abscheck"]="";
      }
      if ($attr->isInTitle) {
	$newelem[$k]["titcheck"]="checked";
      } else {
	$newelem[$k]["titcheck"]="";
      }

      $newelem[$k]["neededcheck"]=($attr->needed)?"checked":"";

      if ($attr->docid == $docid) {
	$newelem[$k]["disabled"]="";
      } else {
	$newelem[$k]["disabled"]="disabled";
      }

      $newelem[$k]["typevalue"]=$attr->type;




      while(list($kopt,$opt) = each($selectframe))  {
	if ($opt["frameid"] == $attr->fieldSet->id){
	  $selectframe[$kopt]["selected"]="selected"; 
	}else{
	  $selectframe[$kopt]["selected"]=""; 
	}
		  
      }

      $newelem[$k]["SELECTOPTION"]="SELECTOPTION_$k";
      $action->lay->SetBlockData($newelem[$k]["SELECTOPTION"],
				 $selectoption);

      $newelem[$k]["SELECTFRAME"]="SELECTFRAME_$k";
      $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"],
				 $selectframe);
	      
      $ka++;
    }
      
    
  }


  // reset default values
  while(list($kopt,$opt) = each($selectframe))  $selectframe[$kopt]["selected"]=""; 
  while(list($kopt,$opt) = each($selectoption))  $selectoption[$kopt]["selected"]=""; 
    

  $action->lay->SetBlockData("SELECTCLASS", $selectclass);


  // add 3 new attributes to be defined

  
  for ($k=$ka;$k<3+$ka;$k++) {
    $newelem[$k]["neweltid"]=$k;
    $newelem[$k]["attrname"]="";
    $newelem[$k]["disabledid"]="";
    $newelem[$k]["typevalue"]="";
    $newelem[$k]["visibility"]="W";
    $newelem[$k]["link"]="";
    $newelem[$k]["elink"]="";
    $newelem[$k]["phpfile"]="";
    $newelem[$k]["phpfunc"]="";
    $newelem[$k]["order"]="";
    $newelem[$k]["attrid"]="";
    $newelem[$k]["SELECTOPTION"]="SELECTOPTION_$k";
    $action->lay->SetBlockData($newelem[$k]["SELECTOPTION"],
			       $selectoption);

    $newelem[$k]["SELECTFRAME"]="SELECTFRAME_$k";
    $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"],
			       $selectframe);
    $newelem[$k]["disabled"]="";
  }



  $action->lay->SetBlockData("NEWELEM",$newelem);

}

?>
