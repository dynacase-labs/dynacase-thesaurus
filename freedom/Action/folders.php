<?php
// ---------------------------------------------------------------
// $Id: folders.php,v 1.5 2001/11/21 17:03:54 eric Exp $
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
  $nbfolders=1;


  // Set Css
  $cssfile=$action->GetLayoutFile("freedom.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());

  // Get all the params      
  $dirid=GetHttpVars("dirid",0); // root directory

  $dbaccess = $action->GetParam("FREEDOM_DB");



  

  $tmenuaccess = array(); // to define action an each icon

  $oqdv = new QueryDirV($dbaccess);

  if ($dirid == 0) $dirid=$oqdv->getFirstDir();

  
  $doc = new Doc($dbaccess, $dirid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("reptitle", $doc->title);

  $lpopup = new Layout($action->GetLayoutFile("popup.js"),$action);

  // ------------------------------------------------------
  // definition of popup menu
  $menuitems= array('vprop','mkdir','cancel','staticpaste','pastelatest','cancel2');
  while (list($ki, $imenu) = each($menuitems)) {
    $lpopup->Set("menuitem$ki",$imenu);
    ${$imenu} = "vmenuitem$ki";
  }
  $lpopup->Set("nbmitem", 6);

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
  // css pour popup
  $cssfile=$action->GetLayoutFile("popup.css");
  $csslay = new Layout($cssfile,$action);
  $action->parent->AddCssCode($csslay->gen());
  // display popup js




  $lpopup->Set("nbdiv",$nbfolders);
  for ($i=0; $i<$nbfolders; $i++) {
      $tmenuaccess[$i]["divid"] = $i;
    $tmenuaccess[$i][$cancel]=1;
    $tmenuaccess[$i][$vprop]=1;
    $tmenuaccess[$i][$mkdir]=1;
    $tmenuaccess[$i][$staticpaste]=1;
    $tmenuaccess[$i][$pastelatest]=1;
    $tmenuaccess[$i][$cancel2]=1;
      // unused menu items
    $tmenuaccess[$i]["vmenuitem6"]=0;
    $tmenuaccess[$i]["vmenuitem7"]=0;
    $tmenuaccess[$i]["vmenuitem8"]=0;
    $tmenuaccess[$i]["vmenuitem9"]=0;

  }
  $lpopup->SetBlockData("MENUACCESS", $tmenuaccess);

  $action->parent->AddJsCode($lpopup->gen());
  
}


// -----------------------------------
function addfolder($doc, $level) {
// -----------------------------------
  global $oqdv;
  global $tmenuaccess;
  global $nbfolders;
  
  $levelp = $level-1;
     $ltree = "aux$level = insFld(aux".$levelp.", gFld(\"".$doc->title."\", \"#\",".$doc->id."))\n";


  $nbfolders++;


  $ldir = $oqdv->getChildDir($doc->id);
  

  if (count($ldir) > 0 ) {
     
       while (list($k,$v) = each($ldir)) {
      $ltree .= addfolder($v, $level+1);
     }
  } 
  return $ltree;
}

?>