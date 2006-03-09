<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.Mask.php,v 1.15 2006/03/09 09:41:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Method.Mask.php,v 1.15 2006/03/09 09:41:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Mask.php,v $
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


var $defaultedit= "FREEDOM:EDITMASK";
var $defaultview= "FREEDOM:VIEWMASK";

function SpecRefresh() {
 
  //  gettitle(D,AR_IDCONST):AR_CONST,AR_IDCONST
  $this->refreshDocTitle("MSK_FAMID","MSK_FAM");

  
  return $err;
}

function getLabelVis() {
  return  array("-" => " ",
		    "R" => _("read only"),
		    "W" => _("read write"),
		    "O" => _("write only"),
		    "H" => _("hidden"),
		    "S" => _("read disabled"),
		    "I" => _("invisible"));
}
function getLabelNeed() {
  return  array("-" => " ",
		"Y" => _("Y"),
		"N" => _("N"));
}


function getVisibilities() {
  $tvisid = $this->getTValue("MSK_VISIBILITIES");
  $tattrid = $this->getTValue("MSK_ATTRIDS");

  $tvisibilities=array();
  while (list($k,$v)= each ($tattrid)) {
    $tvisibilities[$v]=$tvisid[$k];    
  }
  return $tvisibilities;
}

function getCVisibilities() {
  $tvisid = $this->getTValue("MSK_VISIBILITIES");
  $tattrid = $this->getTValue("MSK_ATTRIDS");
  $docid = $this->getValue("MSK_FAMID",1);
  $doc= new_Doc($this->dbaccess,$docid);

  $tsvis = $this->getVisibilities();
  $tvisibilities=array();

  foreach($tattrid as $k=>$v) {
    $attr = $doc->getAttribute($v);
    $fvisid=$attr->fieldSet->id;
    if ($tvisid[$k]=="-") $vis=$attr->visibility;
    else $vis=$tvisid[$k];

    $tvisibilities[$v]=ComputeVisibility($vis,$tvisibilities[$fvisid]);    
  }
  return $tvisibilities;
}
function getNeedeeds() {
  $tvisid = $this->getTValue("MSK_NEEDEEDS");
  $tattrid = $this->getTValue("MSK_ATTRIDS");

  $tvisibilities=array();
  while (list($k,$v)= each ($tattrid)) {
    $tvisibilities[$v]=$tvisid[$k];    
  }
  return $tvisibilities;
}

function viewmask($target="_self",$ulink=true,$abstract=false) {
 
  $docid = $this->getValue("MSK_FAMID",1);

  $tvisibilities=$this->getCVisibilities();
  $tneedeeds=$this->getNeedeeds();

  $this->lay->Set("docid",$docid);

  $doc= new_Doc($this->dbaccess,$docid);


  // display current values
  $tmask=array();
  
 
  $labelvis = $this->getLabelVis();
  
    
  $tattr = $doc->GetFieldAttributes();
  $tattr = $doc->GetNormalAttributes();
  $tattr += $doc->GetActionAttributes();
  foreach($tattr as $k=>$attr) {
    
    if ((isset($attr->fieldSet))&&
	($attr->fieldSet->visibility == "H")) $this->attributes->attr[$k]->mvisibility="H";
  }
 



  //  --------------------  ----------------------
  
  uasort($tattr,"tordered"); 

  foreach($tattr as $k=>$attr) {
    $tmask[$k]["attrname"]=$attr->labelText;
    $tmask[$k]["visibility"]=$labelvis[$attr->visibility];
    $tmask[$k]["wneed"]=($attr->needed)?"bold":"normal";
    $tmask[$k]["bgcolor"]="";
    $tmask[$k]["vislabel"] = " ";
    if (isset($tvisibilities[$attr->id])) {
      $tmask[$k]["vislabel"] = $labelvis[$tvisibilities[$attr->id]];
      if ($tmask[$k]["visibility"] != $tmask[$k]["vislabel"]) $tmask[$k]["bgcolor"]=getParam("CORE_BGCOLORALTERN");
    } else $tmask[$k]["vislabel"] = $labelvis["-"];

    
    if (isset($tneedeeds[$attr->id])) {
      if (($tneedeeds[$attr->id]=="Y") || (($tneedeeds[$attr->id]=="-") && ($attr->needed)))  $tmask[$k]["waneed"] = "bold";
      else $tmask[$k]["waneed"] = "normal";
      if ($tneedeeds[$attr->id] != "-") $tmask[$k]["bgcolor"]=getParam("CORE_BGCOLORALTERN");
    } else $tmask[$k]["waneed"] = "normal";

 
    // display visibility case of change in needed only
    if (($tmask[$k]["vislabel"]==" ") && ($tneedeeds[$attr->id] != "-")) $tmask[$k]["vislabel"]=$labelvis[$attr->visibility];


    if ($attr->docid == $docid) {
      $tmask[$k]["disabled"]="";
    } else {
      $tmask[$k]["disabled"]="disabled";
    }


    $tmask[$k]["framelabel"]=$attr->fieldSet->labelText;
    if ($attr->waction!="") $tmask[$k]["framelabel"]=_("Action");

  }

  $this->lay->SetBlockData("MASK",$tmask);  
}



function editmask() {
 
  $docid = $this->getValue("MSK_FAMID",1);


  $this->lay->Set("docid",$docid);

  $doc= new_Doc($this->dbaccess,$docid);


  $tvisibilities=$this->getVisibilities();
  $tneedeeds=$this->getNeedeeds();
  
  $selectclass=array();
  $tclassdoc = GetClassesDoc($this->dbaccess, $this->userid,0,"TABLE");
  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["id"];
    $selectclass[$k]["classname"]=$cdoc["title"];
    $selectclass[$k]["selected"]="";
  }


  $selectframe= array();

  $nbattr=0; // if new document 

  // display current values
  $newelem=array();

   

  // selected the current class document
  while (list($k,$cdoc)= each ($selectclass)) {

    if ($docid == $selectclass[$k]["idcdoc"]) {

      $selectclass[$k]["selected"]="selected";
    }
    
  }

  $this->lay->SetBlockData("SELECTCLASS", $selectclass);


  $ka = 0; // index attribute

  
  $labelvis=$this->getLabelVis();      
  while(list($k,$v) = each($labelvis))  {
    $selectvis[] = array("visid" =>$k ,
			 "vislabel" => $v);
  }
  $labelneed=$this->getLabelNeed();      
  while(list($k,$v) = each($labelneed))  {
    $selectneed[] = array("needid" =>$k ,
			  "needlabel" => $v);
  }
		     

  //    ------------------------------------------
  //  -------------------- NORMAL ----------------------
  $tattr = $doc->GetNormalAttributes();
  $tattr += $doc->GetFieldAttributes();
  $tattr += $doc->GetActionAttributes();
  uasort($tattr,"tordered"); 
  foreach($tattr as $k=>$attr) {
    if ($attr->usefor=="Q") continue; // not parameters
    if ($attr->docid ==0) continue; // not parameters
    $newelem[$k]["attrid"]=$attr->id;
    $newelem[$k]["attrname"]=$attr->labelText;
    $newelem[$k]["visibility"]=$labelvis[$attr->visibility];

    $newelem[$k]["wneed"]=($attr->needed)?"bold":"normal";
    $newelem[$k]["neweltid"]=$k;
    
    if (($attr->type=="array") || (strtolower(get_class($attr)) == "fieldsetattribute"))$newelem[$k]["fieldweight"]="bold";
    else $newelem[$k]["fieldweight"]="";

    if ($attr->docid == $docid) {
      $newelem[$k]["disabled"]="";
    } else {
      $newelem[$k]["disabled"]="disabled";
    }

    if ($attr->fieldSet->docid >0)   $newelem[$k]["framelabel"]=$attr->fieldSet->labelText;
    else  $newelem[$k]["framelabel"]="";
    if ($attr->waction!="") $newelem[$k]["framelabel"]=_("Action");

    reset($selectvis);
    while(list($kopt,$opt) = each($selectvis))  {
      if ($opt["visid"] == $tvisibilities[$attr->id]) {
	$selectvis[$kopt]["selected"]="selected"; 
      } else{
	$selectvis[$kopt]["selected"]=""; 
      }		  
    }
    // idem for needed
    reset($selectneed);
    while(list($kopt,$opt) = each($selectneed))  {
      if ($opt["needid"] == $tneedeeds[$attr->id]) {
	$selectneed[$kopt]["selectedneed"]="selected"; 
      } else{
	$selectneed[$kopt]["selectedneed"]=""; 
      }		  

    }


    $newelem[$k]["SELECTVIS"]="SELECTVIS_$k";
    $this->lay->SetBlockData($newelem[$k]["SELECTVIS"],
			     $selectvis);
    $newelem[$k]["SELECTNEED"]="SELECTNEED_$k";
    $this->lay->SetBlockData($newelem[$k]["SELECTNEED"],
			     $selectneed);
	      
    $ka++;
  }
          

  $this->lay->SetBlockData("NEWELEM",$newelem);

  $this->editattr();
}
?>