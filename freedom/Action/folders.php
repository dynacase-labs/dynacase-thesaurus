<?php
// ---------------------------------------------------------------
// $Id: folders.php,v 1.9 2001/11/27 13:09:08 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/folders.php,v $
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
// $Log: folders.php,v $
// Revision 1.9  2001/11/27 13:09:08  eric
// barmenu & modif popup
//
// Revision 1.8  2001/11/26 18:01:01  eric
// new popup & no lock for no revisable document
//
// Revision 1.7  2001/11/22 17:49:13  eric
// search doc
//
// Revision 1.6  2001/11/22 10:00:59  eric
// premier pas vers une API pour les popup
//
// Revision 1.5  2001/11/21 17:03:54  eric
// modif pour création nouvelle famille
//
// Revision 1.4  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.3  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.2  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.QueryDirV.php");
include_once("FREEDOM/freedom_util.php");  




// -----------------------------------
function folders(&$action) {
  // -----------------------------------

  
  global $oqdv, $nbfolders;
  $nbfolders=0;


  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  // Get all the params      
  $dirid=GetHttpVars("dirid",0); // root directory

  $dbaccess = $action->GetParam("FREEDOM_DB");

  include_once("FREEDOM/popup_util.php");
  barmenu($action); // describe bar menu

  

  $tmenuaccess = array(); // to define action an each icon

  $oqdv = new QueryDirV($dbaccess);

  if ($dirid == 0) $dirid=$oqdv->getFirstDir();

  
  $doc = new Doc($dbaccess, $dirid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("reptitle", $doc->title);


  // ------------------------------------------------------
  // definition of popup menu
  popupInit("popfld", array('vprop','mkdir','cancel'));
  popupInit("poppaste", array('staticpaste','pastelatest','cancel2'));

  // for the first (main) folder
  popupActive("popfld",$nbfolders,'cancel');
  popupActive("popfld",$nbfolders,'vprop');
  popupActive("popfld",$nbfolders,'mkdir');  
  popupActive("poppaste",$nbfolders,'staticpaste');
  popupActive("poppaste",$nbfolders,'pastelatest');
  popupActive("poppaste",$nbfolders,'cancel2');


  $nbfolders++;
  // define sub tree
  $stree="";
  $ldir =   $oqdv->getChildDir($dirid);
  while (list($k,$v) = each($ldir)) {
    $stree .= addfolder($v, 1);
  }
    
  //  print($stree);
  
  $action->lay->Set("subtree", $stree);
  

  //-------------- pop-up menu ----------------
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  // display popup js




  // display popup js
  popupGen($nbfolders);
  
}


// -----------------------------------
function addfolder($doc, $level) {
  // -----------------------------------
  global $oqdv;
  global $tmenuaccess;
  global $nbfolders;
  
  $levelp = $level-1;
  if ($doc->doctype == 'D') $ftype=1;
  if ($doc->doctype == 'S') $ftype=2;
  $ltree = "aux$level = insFld(aux".$levelp.", gFld(\"".$doc->title."\", \"#\",".$doc->id.",$ftype))\n";


  popupActive("popfld",$nbfolders,'cancel');
  popupActive("popfld",$nbfolders,'vprop');
  if ($doc->doctype == 'D') popupActive("popfld",$nbfolders,'mkdir');
  else popupInvisible("popfld",$nbfolders,'mkdir');
  popupActive("poppaste",$nbfolders,'staticpaste');
  popupActive("poppaste",$nbfolders,'pastelatest');
  popupActive("poppaste",$nbfolders,'cancel2');
  $nbfolders++;

  if ($doc->doctype == 'D') {

    $ldir = $oqdv->getChildDir($doc->id);
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree .= addfolder($v, $level+1);
      }
    } 
  }
  return $ltree;
}

// -----------------------------------
function barmenu(&$action) {
  // -----------------------------------
  popupInit("newmenu", array('newdoc','newfld','newprof','newfam'));
  popupInit("searchmenu",	array(   'newsearch'));



  popupInit("viewmenu",	array(  'vlist','vicon','refresh'));
  popupInit("helpmenu", array(  'help'));


    popupActive("newmenu",1,'newdoc'); 
    popupActive("newmenu",1,'newfld'); 
    popupActive("newmenu",1,'newprof');
    popupActive("newmenu",1,'newfam');
    popupActive("searchmenu",1,'newsearch');
    popupActive("viewmenu",1,'vlist');
    popupActive("viewmenu",1,'vicon');
    popupActive("viewmenu",1,'refresh');
    popupActive("helpmenu",1,'help');



}
?>