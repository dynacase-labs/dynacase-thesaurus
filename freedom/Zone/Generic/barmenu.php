<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: barmenu.php,v 1.26 2004/12/28 17:08:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: barmenu.php,v 1.26 2004/12/28 17:08:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Generic/barmenu.php,v $
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

include_once("GENERIC/generic_util.php");  

// -----------------------------------
function barmenu(&$action) {
  // -----------------------------------
  global $dbaccess; // use in getChildCatg function



  $dirid=GetHttpVars("dirid", getDefFld($action) ); // folder where search
  
  $catg=GetHttpVars("catg", 1); // catg where search


  if ($action->Read("navigator","")=="EXPLORER") {
    // special for position style
    $action->lay->set("positionstyle","");
    $action->lay->set("fhelp","_blank");
  } else {
    $action->lay->set("positionstyle","fixed");
    $action->lay->set("fhelp","fhidden");
  }

  $dbaccess = $action->GetParam("FREEDOM_DB");



  $famid = getDefFam($action);
  
  $fdoc= new Doc( $dbaccess, $famid);


  
  if ($catg>1)   $fld=new Doc($dbaccess, $catg);
  else  $fld=new Doc($dbaccess, $dirid);

  $action->lay->set("pds",$fld->urlWhatEncodeSpec("")); // parameters for searches

  if ($fdoc->control("create") == "") {
    $child[$famid] = array("title"=> $fdoc->title,
			   "id" => $famid);
  } else $child=array();
  $child += $fdoc->GetChildFam($fdoc->id,true);
  

  $tchild = array();
  $tnewmenu= array();
  while (list($k,$vid) = each($child)) {
    $tchild[] = array("stitle" => $vid["title"],
		      "subfam" => $vid["id"]);
    $tnewmenu[]="newdoc".$k;
  }
  $action->lay->SetBlockData("NEWFAM", $tchild);
  $action->lay->Set("ftitle", $fdoc->title);

  $action->lay->Set("famid", $famid);
  $action->lay->Set("splitmode", getSplitMode($action,$famid));


  include_once("FDL/popup_util.php");
  //--------------------- kind menu -----------------------
  $lattr = $fdoc->getNormalAttributes();
  
  $tkind=array();
  while (list($k,$a) = each($lattr)) {
    if ((($a->type == "enum") || ($a->type == "enumlist")) &&
	($a->phpfile != "-")) {
      
      $tkind[]=array("kindname"=>$a->labelText,
		     "kindid"=>$a->id,
		     "vkind"=>"kind".$a->id);
      $tvkind=array();
      $tmkind=array();
      $tmkind[]=$a->id."kedit";
      $enum=$a->getenum();
      while (list($kk,$ki) = each($enum)) {
	$tvkind[]=array("ktitle" => strstr($ki, '/')?strstr($ki, '/'):$ki,
			"level" =>  substr_count($kk, '.')*20,
			"kid" => $kk);
	$tmkind[]=$a->id.$kk;
      }
      $action->lay->SetBlockData("kind".$a->id, $tvkind);

      
      popupInit($a->id."menu", $tmkind);
      while (list($km,$vid) = each($tmkind)) {
	popupActive($a->id."menu",1,$vid); 
      }

      if (($a->phpfile != "") || (! $action->HasPermission("GENERIC_MASTER"))) popupInvisible($a->id."menu",1,$a->id."kedit");
    }

  }

  
  $action->lay->SetBlockData("KIND", $tkind);
  $action->lay->SetBlockData("MKIND", $tkind);

  $action->lay->Set("nbcol", 4+count($tkind));
  //--------------------- construction of  menu -----------------------

  popupInit("newmenu",  $tnewmenu   );

  popupInit("helpmenu", array('help','imvcard','folders','isplit','cview','aview'));


  if ($action->HasPermission("GENERIC"))  {

    while (list($k,$vid) = each($tnewmenu)) {
      popupActive("newmenu",1,$vid); 
    }
  } else {

    while (list($k,$vid) = each($tnewmenu)) {
      popupInactive("newmenu",1,$vid); 
    }
  }
  if ($action->HasPermission("GENERIC_MASTER"))  {
    popupActive("helpmenu",1,'imvcard');
  
  }   else {
    popupInvisible("helpmenu",1,'imvcard'); 
  }


  popupActive("helpmenu",1,'isplit'); 
  popupActive("helpmenu",1,'cview'); 
  popupActive("helpmenu",1,'aview'); 

  popupActive("helpmenu",1,'help');

  
  popupInvisible("helpmenu",1,'folders');
  if ($idappfree=$action->parent->Exists("FREEDOM")) {
   
    $permission = new Permission($action->dbaccess, array($action->user->id,$idappfree));
    
    if (($action->user->id==1) || ($permission->isAffected() && (count($permission->privileges) > 0))) {
      popupActive("helpmenu",1,'folders');
    }
  }


  $homefld = new Doc( $dbaccess, getDefFld($action));



  // compute categories and searches
  $stree=getChildCatg( $homefld->id, 1,false,1);

  reset($stree);
  
  $lidsearch = array("catg0");

  $streeSearch = array();
  while (list($k,$v) = each($stree)) {
    if (($v["doctype"] == "S" )&&($v["fromid"] != $fdoc->id) ) {
      $lidsearch[] = "search".$v["id"];
      $streeSearch[] = $v;
    } 
  }
  $lidsearch[]="text";

  popupInit("searchmenu",$lidsearch);
  reset ($lidsearch);
  while (list($k,$v) = each($lidsearch)) {
    popupActive("searchmenu",1,$v);
  }
  
  $action->lay->SetBlockData("SEARCH",$streeSearch);
  $action->lay->Set("topid",getDefFld($action));
  $action->lay->Set("dirid",$dirid);
  $action->lay->Set("catg",$catg);

  //----------------------------
  // sort menu

  
  $tsort = array("title"=>array("said"=>"title",
				"satitle"=>_("doctitle")),
		 "revdate"=>array("said"=>"revdate",
		       "satitle"=>_("revdate")));
  if ($fdoc->wid > 0) {
    $tsort["state"]= array("said"=>"state",
			   "satitle"=>_("state"));
  }
  $tmsort[]="sortdesc";
  while (list($k,$v) = each($tsort)) {
    $tmsort[$v["said"]]="sortdoc".$v["said"];
  }
  $lattr=$fdoc->GetSortAttributes();
  while (list($k,$a) = each($lattr)) {
    
    $tsort[$a->id] = array("said"=>$a->id,
			   "satitle"=>$a->labelText);
    $tmsort[$a->id] = "sortdoc".$a->id;
    
  }
  // select the current sort
  $csort=getDefUSort($action);
  if ($csort[0]=='-') {
    $csort=substr($csort,1);
    $cselect = "&uarr;";
  } else {
    $cselect = "&darr;";
  }
  reset ($tsort);
  while (list($k,$v) = each($tsort)) {
    $tsort[$k]["dsort"]=($csort==$k)?$cselect:"&nbsp;"; // use puce
  }
  popupInit("sortmenu",$tmsort);
  reset ($tmsort);
  while (list($k,$v) = each($tmsort)) {
    popupActive("sortmenu",1,$v);
  }
  $action->lay->SetBlockData("USORT",$tsort);

  popupGen(1);

}



?>
