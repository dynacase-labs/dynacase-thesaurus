<?php
// ---------------------------------------------------------------
// $Id: generic_barmenu.php,v 1.7 2002/11/04 17:56:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_barmenu.php,v $
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
function generic_barmenu(&$action) {
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
  popupInit("newmenu",  array_merge($tnewmenu ,array('newcatg','imvcard'))  );

  popupInit("helpmenu", array('help'));


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
    popupActive("newmenu",1,'newcatg');
    popupActive("newmenu",1,'imvcard');
  }   else {
    popupInvisible("newmenu",1,'newcatg'); 
    popupInvisible("newmenu",1,'imvcard'); 
  }


  popupActive("helpmenu",1,'help');


  $homefld = new Dir( $dbaccess, getDefFld($action));



  // compute categories and searches
  $stree=getChildCatg( $homefld, 1);

  reset($stree);
  
  $lidcatg = array("catg0");
  $lidsearch = array();
  while (list($k,$v) = each($stree)) {
    if ($v["doctype"] == "S" ) {
      $lidsearch[] = "search".$v["id"];
      $streeSearch[] = $v;
    } else {
      $lidcatg[] = "catg".$v["id"];
      $streeCatg[] = $v;
    }
  }
  $lidsearch[]="text";

  popupInit("catgmenu",$lidcatg);
  popupInit("searchmenu",$lidsearch);
  reset ($lidcatg);
  while (list($k,$v) = each($lidcatg)) {
    popupActive("catgmenu",1,$v);
  }
  reset ($lidsearch);
  while (list($k,$v) = each($lidsearch)) {
    popupActive("searchmenu",1,$v);
  }
  
  $action->lay->SetBlockData("CATG",$streeCatg);
  $action->lay->SetBlockData("SEARCH",$streeSearch);
  $action->lay->Set("topid",getDefFld($action));
  $action->lay->Set("dirid",$dirid);
  $action->lay->Set("catg",$catg);

  popupGen(1);

}



?>
