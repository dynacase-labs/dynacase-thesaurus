<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: foliotab.php,v 1.4 2003/08/18 15:47:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: foliotab.php,v 1.4 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/foliotab.php,v $
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
include_once("FDL/freedom_util.php");  




// -----------------------------------
function foliotab(&$action) {
  // -----------------------------------

  // Get all the params      
  $docid=GetHttpVars("id",0); // portfolio id

  $dbaccess = $action->GetParam("FREEDOM_DB");

  include_once("FDL/popup_util.php");
  $nbfolders=1;
  
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
 
  $doc = new Doc($dbaccess,$docid);
  $action->lay->set("docid",$docid);
  $action->lay->set("dirid",$doc->initid);
  $action->lay->set("title",$doc->title);

//   popupInit("poppaste", array('staticpaste','pastelatest','cancel'));

//   popupActive("poppaste",$nbfolders,'staticpaste');
//   popupActive("poppaste",$nbfolders,'pastelatest');
//   popupActive("poppaste",$nbfolders,'cancel');
  $child = getChildDir($dbaccess,$action->user->id,$doc->initid, false,"TABLE");
  

  $ttag=array();
  while(list($k,$v) = each($child)) {

    if ($v["usefor"] == "G") {
      $ttag[] = array(
		      "tabid"=>$v["id"],
		      "doctype"=>$v["doctype"],
		      "TAG_LABELCLASS" => $v["doctype"]=="S"?"searchtab":"",
		      "tag_cellbgclass"=>($v["id"] ==$docid)?"ongletvs":"ongletv",
		      "tabtitle"=>$v["title"]);
      $nbfolders++;
    }
//     popupActive("poppaste",$nbfolders,'staticpaste');
//     popupActive("poppaste",$nbfolders,'pastelatest');
//     popupActive("poppaste",$nbfolders,'cancel');
  }

  // display popup js
//   popupGen($nbfolders);

  $action->lay->setBlockData("TAG",$ttag);
  $action->lay->setBlockData("nbcol",count($ttag)+1);
}

?>