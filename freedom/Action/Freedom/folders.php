<?php
// ---------------------------------------------------------------
// $Id: folders.php,v 1.6 2002/04/03 09:40:15 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/folders.php,v $
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



include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/freedom_util.php");  




// -----------------------------------
function folders(&$action) {
  // -----------------------------------

  
  global  $nbfolders, $dbaccess;
  $nbfolders=0;


  // Get all the params      
  $dirid=GetHttpVars("dirid",0); // root directory

  $dbaccess = $action->GetParam("FREEDOM_DB");

  include_once("FDL/popup_util.php");
  barmenu($action); // describe bar menu

  $homefld = new Dir( $dbaccess);
  $homefld = $homefld->GetHome();

  $action->lay->Set("homename", $homefld->title);
  $action->lay->Set("homeid", $homefld->id);
  

  $tmenuaccess = array(); // to define action an each icon



  if ($dirid == 0) $dirid=getFirstDir($dbaccess);

  
  $doc = new Doc($dbaccess, $dirid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("reptitle", $doc->title);


  // ------------------------------------------------------
  // definition of popup menu
  popupInit("popfld", array('vprop','mkdir','export','cancel'));
  popupInit("poppaste", array('staticpaste','pastelatest','cancel2'));

  // for the first (top) folder
  popupActive("popfld",$nbfolders,'cancel');
  popupActive("popfld",$nbfolders,'vprop');
  popupActive("popfld",$nbfolders,'export');
  popupActive("popfld",$nbfolders,'mkdir');  
  popupActive("poppaste",$nbfolders,'staticpaste');
  popupActive("poppaste",$nbfolders,'pastelatest');
  popupActive("poppaste",$nbfolders,'cancel2');


  $nbfolders++; // one for the top


  // define sub trees

  
  $stree=addfolder($doc, -1, "fldtop", false);
  $action->lay->Set("subtree", $stree);

  $htree=addfolder($homefld, 0, "fldtop");
  $action->lay->Set("hometree", $htree);
  

  //-------------- pop-up menu ----------------
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  // display popup js




  // display popup js
  popupGen($nbfolders);
  
}


// -----------------------------------
function addfolder($doc, $level, $treename, $thisfld=true) {
  // -----------------------------------
  global $dbaccess;
  global $tmenuaccess;
  global $nbfolders;
  

  if ($thisfld) {
    if ($level == 0) $levelp="";
    else $levelp = $level-1;
    if ($doc->owner < 0) $ftype=3;
    else if ($doc->doctype == 'D') $ftype=1;
    else if ($doc->doctype == 'S') $ftype=2;

    $hasChild='false';
    if ($doc->doctype != 'S') {
      // no child for a search
	if (hasChildFld($dbaccess,$doc->id))  $hasChild='true';
    }


    $ltree = "$treename$level = insFld(".$treename.$levelp.", gFld(\"".$doc->title."\", \"#\",".$doc->id.",$ftype, $hasChild))\n";


    popupActive("popfld",$nbfolders,'cancel');
    popupActive("popfld",$nbfolders,'vprop');
    popupActive("popfld",$nbfolders,'export');
    if ($doc->doctype == 'D') popupActive("popfld",$nbfolders,'mkdir');
    else popupInvisible("popfld",$nbfolders,'mkdir');
    popupActive("poppaste",$nbfolders,'staticpaste');
    popupActive("poppaste",$nbfolders,'pastelatest');
    popupActive("poppaste",$nbfolders,'cancel2');
    $nbfolders++;
  } else $ltree = "";
  if ($doc->doctype == 'D') {

    if ($level < 0) {
    $ldir = getChildDir($dbaccess, $doc->id);
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree .= addfolder($v, $level+1, $treename);
      }
    } 
  }
  }
  return $ltree;
}

// -----------------------------------
function barmenu(&$action) {
  // -----------------------------------
  popupInit("newmenu",    array('newdoc','newfld','newprof','newfam','import'));
  popupInit("searchmenu", array( 'newsearch'));



  popupInit("viewmenu",	array('vlist','vicon'));
  popupInit("helpmenu", array('help'));


    popupActive("newmenu",1,'newdoc'); 
    popupActive("newmenu",1,'newfld'); 
    popupActive("newmenu",1,'newprof');
    popupActive("newmenu",1,'newfam');
    popupActive("newmenu",1,'import'); 
    popupActive("searchmenu",1,'newsearch');
    popupActive("viewmenu",1,'vlist');
    popupActive("viewmenu",1,'vicon');
    popupActive("helpmenu",1,'help');



}
?>
