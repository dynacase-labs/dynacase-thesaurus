<?php
// ---------------------------------------------------------------
// $Id: popupcard.php,v 1.5 2002/11/06 15:59:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Generic/popupcard.php,v $
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

include_once("FDL/Class.Doc.php");
// -----------------------------------
function popupcard(&$action) {
  // -----------------------------------
  // ------------------------------
  // define accessibility
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");
  $headers = (GetHttpVars("head",'no') == "yes");

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);
  $kdiv=1; // only one division

  $action->lay->Set("id", $docid);

  include_once("FDL/popup_util.php");
  // ------------------------------------------------------
  // definition of popup menu
  popupInit('popupcard',  array('editdoc','editstate','unlockdoc','chgcatg','properties','duplicate','headers','delete','cancel'));


  $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");


  Popupactive('popupcard',$kdiv,'cancel');


  if ($doc->isLocked()) {
    if ($cuf) popupActive('popupcard',$kdiv,'unlockdoc');
    else popupInactive('popupcard',$kdiv,'unlockdoc');
  } else popupInvisible('popupcard',$kdiv,'unlockdoc'); 

  popupActive('popupcard',$kdiv,'duplicate'); 

 

  if ($doc->locked == -1){ // fixed document
      popupInvisible('popupcard',$kdiv,'editdoc');
      popupInvisible('popupcard',$kdiv,'delete');
      popupInvisible('popupcard',$kdiv,'unlockdoc');
      popupInvisible('popupcard',$kdiv,'chgcatg'); 
      popupInvisible('popupcard',$kdiv,'editstate'); 
  } else {
    if ($cud || $clf)   {
      popupActive('popupcard',$kdiv,'editdoc');
      $action->lay->Set("deltitle", $doc->title);
      popupInvisible('popupcard',$kdiv,'editstate'); 
      popupActive('popupcard',$kdiv,'delete');
      popupActive('popupcard',$kdiv,'chgcatg'); 
    }  else   {
      popupInactive('popupcard',$kdiv,'editdoc');
      popupInactive('popupcard',$kdiv,'delete');
      popupInactive('popupcard',$kdiv,'chgcatg'); 
      
      if ($doc->wid > 0) {
	$wdoc=new Doc($doc->dbaccess, $doc->wid);
	$wdoc->Set($doc);
	if (count($wdoc->GetFollowingStates()) > 0)
	  popupActive('popupcard',$kdiv,'editstate');
      } else popupInvisible('popupcard',$kdiv,'editstate'); 
    }
  }
  
  if ($abstract) popupActive('popupcard',$kdiv,'properties'); 
  else popupInvisible('popupcard',$kdiv,'properties'); 
  if ($headers)  popupInvisible('popupcard',$kdiv,'headers');
  else Popupactive('popupcard',$kdiv,'headers');

  popupGen($kdiv);
}
