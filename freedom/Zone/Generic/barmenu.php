<?php
// ---------------------------------------------------------------
// $Id: barmenu.php,v 1.6 2003/03/05 16:49:28 eric Exp $
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


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $famid = getDefFam($action);
  
  $fdoc= new Doc( $dbaccess, $famid);
  $child[-1] = $famid;
  $child += $fdoc->GetChildFam();
  
  $tchild = array();
  $tnewmenu= array();
  while (list($k,$vid) = each($child)) {
    $cdoc= new Doc( $dbaccess, $vid);
    $tchild[] = array("stitle" => $cdoc->title,
		      "subfam" => $vid);
    $tnewmenu[]="newdoc$vid";
  }

  $action->lay->SetBlockData("NEWFAM", $tchild);
  $action->lay->Set("ftitle", $fdoc->title);

  $action->lay->Set("idfamuser", $famid);


  include_once("FDL/popup_util.php");
  //--------------------- kind menu -----------------------
  $lattr = $fdoc->getNormalAttributes();
  
  $tkind=array();
  while (list($k,$a) = each($lattr)) {
    if ((($a->type == "enum") || ($a->type == "enumlist")) &&
	($a->phpfile == "")) {
      
      $tkind[]=array("kindname"=>$a->labelText,
		     "kindid"=>$a->id,
		     "vkind"=>"kind".$a->id);
      $tvkind=array();
      $tmkind=array($a->id."00");
      while (list($kk,$ki) = each($a->enum)) {
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
    }

  }

  
  $action->lay->SetBlockData("KIND", $tkind);
  $action->lay->SetBlockData("MKIND", $tkind);

  $action->lay->Set("nbcol", 3+count($tkind));
  //--------------------- construction of  menu -----------------------

  popupInit("newmenu",  $tnewmenu   );

  popupInit("helpmenu", array('help','imvcard','folders'));


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


  popupActive("helpmenu",1,'help');
  popupActive("helpmenu",1,'folders');


  $homefld = new Dir( $dbaccess, getDefFld($action));



  // compute categories and searches
  $stree=getChildCatg( $homefld->id, 1,false,1);

  reset($stree);
  
  $lidsearch = array("catg0");

  $streeSearch = array();

  while (list($k,$v) = each($stree)) {
    if ($v["doctype"] == "S" ) {
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

  popupGen(1);

}



?>
