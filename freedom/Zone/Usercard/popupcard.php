<?php
// ---------------------------------------------------------------
// $Id: popupcard.php,v 1.3 2002/03/01 09:36:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Usercard/Attic/popupcard.php,v $
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

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);
  $kdiv=1; // only one division

  $action->lay->Set("id", $docid);

  include_once("FDL/popup_util.php");
  // ------------------------------------------------------
  // definition of popup menu
  popupInit('popupcard',  array('editdoc','unlockdoc','vcard','chgcatg','properties','delete','cancel'));


  $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");


  Popupactive('popupcard',$kdiv,'cancel');


 
  popupInvisible('popupcard',$kdiv,'unlockdoc'); // don't use for the moment

 

  if ($cud || $clf)   {
    popupActive('popupcard',$kdiv,'editdoc');
    $action->lay->Set("deltitle", $doc->title);
    popupActive('popupcard',$kdiv,'delete');
    popupActive('popupcard',$kdiv,'chgcatg'); 
  }  else   {
    popupInactive('popupcard',$kdiv,'editdoc');
    popupInactive('popupcard',$kdiv,'delete');
    popupInactive('popupcard',$kdiv,'chgcatg'); 
  }

  if ($doc->locked < 0){ // fixed document
      popupInvisible('popupcard',$kdiv,'editdoc');
      popupInvisible('popupcard',$kdiv,'delete');
      popupInvisible('popupcard',$kdiv,'unlockdoc');
      popupInvisible('popupcard',$kdiv,'chgcatg'); 
  } 
  
  if ($doc->doctype=="F") popupActive('popupcard',$kdiv,'vcard'); 
  else popupInvisible('popupcard',$kdiv,'vcard');
  if ($abstract) popupActive('popupcard',$kdiv,'properties'); 
  else popupInvisible('popupcard',$kdiv,'properties'); 



  popupGen($kdiv);
}
