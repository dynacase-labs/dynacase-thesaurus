<?php
// ---------------------------------------------------------------
// $Id: usercard_barmenu.php,v 1.5 2002/03/15 16:02:53 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Usercard/Attic/usercard_barmenu.php,v $
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


// -----------------------------------
function usercard_barmenu(&$action) {
  // -----------------------------------
  global $dbaccess; // use in getChildCatg function

  $dirid=GetHttpVars("dirid", TOP_USERDIR); // folder where search

  $action->lay->Set("idfamuser", $action->GetParam("IDFAMUSER", FAM_DOCUSER));

  include_once("FDL/popup_util.php");
  popupInit("newmenu",    array('newdoc','newcatg','imvcard'));
  popupInit("searchmenu", array('text' ));
  popupInit("helpmenu", array('help'));


  if ($action->HasPermission("USERCARD"))   popupActive("newmenu",1,'newdoc'); 
  else popupInactive("newmenu",1,'newdoc'); 
  if ($action->HasPermission("USERCARD_MASTER"))  {
    popupActive("newmenu",1,'newcatg');
    popupActive("newmenu",1,'imvcard');
  }   else {
    popupInvisible("newmenu",1,'newcatg'); 
    popupInvisible("newmenu",1,'imvcard'); 
  }

  popupActive("searchmenu",1,'text');

  popupActive("helpmenu",1,'help');


  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new Dir( $dbaccess, TOP_USERDIR);


  $stree=getChildCatg( $homefld, 1);

  reset($stree);
  
  $lidcatg = array("catg0");
  while (list($k,$v) = each($stree)) {
    $lidcatg[] = "catg".$v["id"];
  }
  popupInit("catgmenu",$lidcatg);
  reset ($lidcatg);
  while (list($k,$v) = each($lidcatg)) {
    popupActive("catgmenu",1,$v);
  }
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topid",TOP_USERDIR);
  $action->lay->Set("dirid",$dirid);

  popupGen(1);

}


// -----------------------------------
function getChildCatg($doc, $level) {
  // -----------------------------------
  global $dbaccess;

  $ltree=array();


    $ldir = getChildDir($dbaccess, $doc->id, true);
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree[] = array("level"=>$level*10,
			 "id"=>$v->id,
			 "title"=>$v->title);
	$ltree = array_merge($ltree, getChildCatg($v, $level+1));
      }
    } 
  
  return $ltree;
}
?>
