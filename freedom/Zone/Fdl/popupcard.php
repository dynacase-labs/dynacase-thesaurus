<?php
// ---------------------------------------------------------------
// $Id: popupcard.php,v 1.31 2003/07/24 12:53:00 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/popupcard.php,v $
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
  $headers = (GetHttpVars("props",'N') == "Y"); // view doc properties

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new Doc($dbaccess, $docid);
  $kdiv=1; // only one division

  $action->lay->Set("id", $docid);
  $action->lay->Set("profid", $doc->profid);
  $action->lay->Set("ddocid", $doc->ddocid); // default doc id for pre-inserted values
  include_once("FDL/popup_util.php");
  // ------------------------------------------------------
  // definition of popup menu
  popupInit('popupcard',  array(
				'headers',
				'latest',
				'editdoc',
				'lockdoc',
				'unlockdoc',
				'revise',
				'duplicate',
				'histo',
				'editprof',
				'access',
				'delete',
				'toxml',
				'tobasket',
				'addpostit',

				'chicon',
				'chgtitle',
				'defval',
				'editattr',
				'editcprof',
				'editstate',
				'editdfld',
				'editwdoc',
				'editcfld',
				'properties',
				'cancel'));


  $clf = ($doc->CanLockFile() == "");
  $cuf = ($doc->CanUnLockFile() == "");
  $cud = ($doc->CanUpdateDoc() == "");

  popupCtrlActive('popupcard',$kdiv,'toxml'); 

  if (getParam("FREEDOM_IDBASKET") > 0)   popupCtrlActive('popupcard',$kdiv,'tobasket'); 
  else popupInvisible('popupcard',$kdiv,'tobasket');

  Popupactive('popupcard',$kdiv,'cancel');
  if (($doc->doctype=="C") && ($cud)) {
    
    popupActive('popupcard',$kdiv,'chicon'); 
  } else {
    popupInvisible('popupcard',$kdiv,'chicon');
  }

  if ($doc->locked == $action->user->id) popupInvisible('popupcard',$kdiv,'lockdoc');
  else if (($doc->locked != $action->user->id) && 
	   $clf) popupCtrlActive('popupcard',$kdiv,'lockdoc');
  else popupInvisible('popupcard',$kdiv,'lockdoc');

  if ($doc->isLocked()) {
    if ($cuf) popupActive('popupcard',$kdiv,'unlockdoc');
    else popupInactive('popupcard',$kdiv,'unlockdoc');
  } else popupInvisible('popupcard',$kdiv,'unlockdoc'); 

  if (! $doc->isRevisable()) popupInvisible('popupcard',$kdiv,'revise');
  else if (($doc->lmodify == 'Y') && 
	   ($cud||$clf)) popupCtrlActive('popupcard',$kdiv,'revise'); 
  else popupCtrlInactive('popupcard',$kdiv,'revise');


  if ($doc->IsControlled() && ($doc->profid > 0) && ($doc->Control("viewacl") == "")) {
    popupCtrlActive('popupcard',$kdiv,'access');
  } else {
    popupInvisible('popupcard',$kdiv,'access');
  }

  if ($doc->Control("modifyacl") == "") {
    popupCtrlActive('popupcard',$kdiv,'editprof'); 
    popupActive('popupcard',$kdiv,'editcprof');
  } else {
    popupCtrlInactive('popupcard',$kdiv,'editprof');
    popupInactive('popupcard',$kdiv,'editcprof');
  }
  if ($doc->PreDelete() == "") {
    $action->lay->Set("deltitle", AddSlashes($doc->title));
    popupActive('popupcard',$kdiv,'delete');    
  } else {
    popupInactive('popupcard',$kdiv,'delete');
  }

  popupInvisible('popupcard',$kdiv,'editstate'); 

  popupInvisible('popupcard',$kdiv,'latest');


  if (($clf)||($cud)) {
    popupActive('popupcard',$kdiv,'editattr'); 
    popupActive('popupcard',$kdiv,'chgtitle'); 
    popupActive('popupcard',$kdiv,'defval'); 
    popupActive('popupcard',$kdiv,'editdoc');
    popupActive('popupcard',$kdiv,'editdfld');
    popupActive('popupcard',$kdiv,'editwdoc');
    popupActive('popupcard',$kdiv,'editcfld');
    
  }  else {
    popupInactive('popupcard',$kdiv,'editattr'); 
    popupInactive('popupcard',$kdiv,'editdfld');
    popupInactive('popupcard',$kdiv,'editwdoc');
    popupInactive('popupcard',$kdiv,'editcfld');
    popupInactive('popupcard',$kdiv,'chgtitle'); 
    popupInactive('popupcard',$kdiv,'defval'); 
    popupCtrlInactive('popupcard',$kdiv,'editprof');
    popupInactive('popupcard',$kdiv,'editdoc');
      

  }
  if ($doc->locked == -1) { // fixed document
    if ($doc->doctype != 'Z') popupActive('popupcard',$kdiv,'latest');
    popupInvisible('popupcard',$kdiv,'editdoc');
    popupInvisible('popupcard',$kdiv,'delete');
    popupInvisible('popupcard',$kdiv,'editattr'); 
    popupInvisible('popupcard',$kdiv,'chgtitle'); 
    popupInvisible('popupcard',$kdiv,'defval'); 
    popupInvisible('popupcard',$kdiv,'editprof');
    popupInvisible('popupcard',$kdiv,'revise');
    popupInvisible('popupcard',$kdiv,'lockdoc');
    popupInvisible('popupcard',$kdiv,'chicon');
    popupInvisible('popupcard',$kdiv,'editwdoc');
    popupInvisible('popupcard',$kdiv,'editdfld');
    popupInvisible('popupcard',$kdiv,'editcfld');
  } 

  popupCtrlActive('popupcard',$kdiv,'duplicate'); 

  if ($doc->locked != -1) {
      if ($doc->wid > 0) {
	$wdoc=new Doc($doc->dbaccess, $doc->wid);
	if ($wdoc->isAlive()) {
	  $wdoc->Set($doc);
	  if (count($wdoc->GetFollowingStates()) > 0)  popupActive('popupcard',$kdiv,'editstate');
	  else popupInactive('popupcard',$kdiv,'editstate');
	}
      }
  }
  if (($doc->doctype=="F") || ($doc->revision > 0)) popupCtrlActive('popupcard',$kdiv,'histo'); 
  else popupInvisible('popupcard',$kdiv,'histo');

  if ($abstract) popupActive('popupcard',$kdiv,'properties'); 
  else popupInvisible('popupcard',$kdiv,'properties'); 


  if (($doc->doctype != "C") || (! $action->HasPermission("FAMILY")) ) {
    
    popupInvisible('popupcard',$kdiv,'editcprof'); 
    popupInvisible('popupcard',$kdiv,'chgtitle'); 
    popupInvisible('popupcard',$kdiv,'defval'); 
    popupInvisible('popupcard',$kdiv,'editattr'); 
    popupInvisible('popupcard',$kdiv,'editdfld');
    popupInvisible('popupcard',$kdiv,'editwdoc');
    popupInvisible('popupcard',$kdiv,'editcfld');
    popupInvisible('popupcard',$kdiv,'chicon');
  }

  if ($doc->doctype == "C") {
    popupInvisible('popupcard',$kdiv,'toxml');
    popupInvisible('popupcard',$kdiv,'editdoc');
    popupInvisible('popupcard',$kdiv,'editstate'); 
    popupInvisible('popupcard',$kdiv,'delete');
    if ($doc->dfldid == 0)  popupInactive('popupcard',$kdiv,'editcfld');
  }

  // if ($doc->doctype == "S") popupInvisible('popupcard',$kdiv,'editdoc'); 

  if ($headers)  popupInvisible('popupcard',$kdiv,'headers');
  else PopupCtrlactive('popupcard',$kdiv,'headers');


  if ($doc->postitid > 0) popupInvisible('popupcard',$kdiv,'addpostit');
  else PopupCtrlactive('popupcard',$kdiv,'addpostit');

  popupGen($kdiv);
}