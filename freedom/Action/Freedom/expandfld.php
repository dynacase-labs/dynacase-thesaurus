<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: expandfld.php,v 1.18 2005/09/27 16:16:50 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: expandfld.php,v 1.18 2005/09/27 16:16:50 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/expandfld.php,v $
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



include_once("FREEDOM/folders.php");  





// -----------------------------------
function expandfld(&$action) {
  // -----------------------------------
    

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $dirid=GetHttpVars("dirid",9); // root directory
  $inavmode=GetHttpVars("inavmode"); // root directory
  $dir = new_Doc($dbaccess, $dirid);
  $navigate=$action->getParam('FREEDOM_VIEWFRAME'); // standard navigation
  
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("reptitle", $dir->title);

  $action->lay->Set("navmode",$navigate);
  if ($inavmode=='inverse') {
    if ($navigate=='navigator') $action->lay->Set("navmode","folder");
    else  if ($navigate=='folder') $action->lay->Set("navmode","navigator");
  } 


  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/expandfld.js");

  // get export permission
  global $core;
  $appfld=new Application();
  $appfld->Set("FDL",$core);
  $pexport=$appfld->HasPermission("EXPORT");
  
  
  // ------------------------------------------------------
    // definition of popup menu
      include_once("FDL/popup_util.php");
  popupInit("popfld", array('vprop','mkdir','export','refresh','cancel'));
  popupInit("poppaste", array('staticpaste','pastelatest','cancel2'));
  $ldir = getChildDir($dbaccess,$action->user->id, $dir->id, false,"TABLE");
  $stree = "";
  if (count($ldir) > 0 ) {
    $nbfolders=1;
    while (list($k,$doc) = each($ldir)) {
      
      
      popupActive("popfld",$nbfolders,'cancel');
      popupActive("popfld",$nbfolders,'vprop');

      if ($pexport)  popupActive("popfld",$nbfolders,'export');
      else popupInvisible("popfld",$nbfolders,'export');

      if ($doc["doctype"] == 'D') {
	popupActive("popfld",$nbfolders,'mkdir');
	popupActive("popfld",$nbfolders,'refresh');
      } else {
	popupInvisible("popfld",$nbfolders,'mkdir');
	popupInvisible("popfld",$nbfolders,'refresh');
      }
      popupActive("poppaste",$nbfolders,'staticpaste');
      popupActive("poppaste",$nbfolders,'pastelatest');
      popupActive("poppaste",$nbfolders,'cancel2');
      $nbfolders++;
      
      if ($doc["owner"] < 0) $ftype=3;
      else if ($doc["doctype"] == 'D') $ftype=1;
      else if ($doc["doctype"] == 'S') $ftype=2;
      else continue; // it 'is not a folder
      $hasChild='false';
     
	// no child for a search

	  if (hasChildFld($dbaccess,$doc["initid"],($doc["doctype"] == 'S')))  $hasChild='true';
      
      
      $ftype=$dir->getIcon($doc["icon"]);
      $stree .= "ffolder.insFld(fldtop, ffolder.gFld(\"".str_replace('"','\"',$doc["title"])."\", \"#\",".$doc["initid"].",\"$ftype\",$hasChild))\n";

      
      
    }
  } 
  
  // define icon from style
  $iconfolder = $action->GetImageUrl("ftv2folderopen1.gif");
  $pathicon = explode("/",$iconfolder);
  if (count($pathicon) == 4) $action->lay->set("iconFolderPath",$pathicon[0]."/".$pathicon[1]);
  else $action->lay->set("iconFolderPath","FREEDOM");
  
  $action->lay->Set("subtree", $stree);
  
  // display popup js
    popupGen($nbfolders);
}
?>