<?php
// ---------------------------------------------------------------
// $Id: popupcard.php,v 1.2 2002/02/05 16:34:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Attic/popupcard.php,v $
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
  popupInit('popupcard',  array('chicon','editdoc','lockdoc','revise','unlockdoc','editattr','histo','editprof','editcprof','properties','cancel'));


  $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");


  Popupactive('popupcard',$kdiv,'cancel');
  if (($doc->doctype=="C") && ($cud)) popupActive('popupcard',$kdiv,'chicon'); 
  else popupInvisible('popupcard',$kdiv,'chicon');

  if (! $doc->isRevisable() ) popupInvisible('popupcard',$kdiv,'lockdoc');
  else if (($doc->locked != $action->user->id) && 
      $clf) popupActive('popupcard',$kdiv,'lockdoc');
  else popupInactive('popupcard',$kdiv,'lockdoc');

  if (! $doc->isRevisable() ) popupInvisible('popupcard',$kdiv,'unlockdoc');
  elseif (($doc->locked != 0) && $cuf) popupActive('popupcard',$kdiv,'unlockdoc'); 
  else popupInactive('popupcard',$kdiv,'unlockdoc');

  if (! $doc->isRevisable()) popupInvisible('popupcard',$kdiv,'revise');
  else if (($doc->lmodify == 'Y') && 
	   ($cud)) popupActive('popupcard',$kdiv,'revise'); 
  else popupInactive('popupcard',$kdiv,'revise');



  if ($doc->Control("modifyacl") == "") {
    popupActive('popupcard',$kdiv,'editprof'); 
    popupActive('popupcard',$kdiv,'editcprof');
  } else {
    popupInactive('popupcard',$kdiv,'editprof');
    popupInactive('popupcard',$kdiv,'editcprof');
  }
  if ($cud) {
    popupActive('popupcard',$kdiv,'editattr'); 
    popupActive('popupcard',$kdiv,'editdoc');
  } else {
    if ($doc->locked < 0){ // fixed document
      popupInvisible('popupcard',$kdiv,'editdoc');
      popupInvisible('popupcard',$kdiv,'editattr'); 
      popupInvisible('popupcard',$kdiv,'editprof');
      popupInvisible('popupcard',$kdiv,'revise');
      popupInvisible('popupcard',$kdiv,'lockdoc');
      popupInvisible('popupcard',$kdiv,'unlockdoc');
      popupInvisible('popupcard',$kdiv,'chicon');
    } else {
      popupInactive('popupcard',$kdiv,'editattr'); 
      popupInactive('popupcard',$kdiv,'editprof');
      popupInactive('popupcard',$kdiv,'editdoc');
    }
  }
  if ($doc->doctype=="F") popupActive('popupcard',$kdiv,'histo'); 
  else popupInvisible('popupcard',$kdiv,'histo');

  if ($abstract) popupActive('popupcard',$kdiv,'properties'); 
  else popupInvisible('popupcard',$kdiv,'properties'); 


  if ($doc->doctype != "C") {
    popupInvisible('popupcard',$kdiv,'editcprof'); 
    popupInvisible('popupcard',$kdiv,'editattr'); 
  }

  if ($doc->doctype == "S") popupInvisible('popupcard',$kdiv,'editdoc'); 

  popupGen($kdiv);
}