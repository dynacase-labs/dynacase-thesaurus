<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: popupcard.php,v 1.41 2004/02/12 10:28:29 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: popupcard.php,v 1.41 2004/02/12 10:28:29 eric Exp $
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
				'sview',
				'sedit',
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
				'reference',
				'tobasket',
				'addpostit',

				'chicon',
				'chgtitle',
				'param',
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
  
  popupInvisible('popupcard',$kdiv,'toxml'); // don't display for the moment
  popupCtrlActive('popupcard',$kdiv,'reference'); 

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
    popupCtrlActive('popupcard',$kdiv,'delete');    
  } else {
    popupInactive('popupcard',$kdiv,'delete');
  }

  popupInvisible('popupcard',$kdiv,'editstate'); 

  popupInvisible('popupcard',$kdiv,'latest');


  if (($clf)||($cud)) {
    popupActive('popupcard',$kdiv,'editattr'); 
    popupActive('popupcard',$kdiv,'chgtitle'); 
    popupActive('popupcard',$kdiv,'defval'); 
    popupActive('popupcard',$kdiv,'param'); 
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
    popupInactive('popupcard',$kdiv,'param'); 
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
    popupInvisible('popupcard',$kdiv,'param'); 
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

  popupActive('popupcard',$kdiv,'histo'); 
  

  if ($abstract) popupActive('popupcard',$kdiv,'properties'); 
  else popupInvisible('popupcard',$kdiv,'properties'); 


  if (($doc->doctype != "C") || (! $action->HasPermission("FAMILY")) ) {
    
    popupInvisible('popupcard',$kdiv,'editcprof'); 
    popupInvisible('popupcard',$kdiv,'chgtitle'); 
    popupInvisible('popupcard',$kdiv,'defval'); 
    popupInvisible('popupcard',$kdiv,'param'); 
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

  if (getParam("FREEDOM_IDBASKET",-1) == -1) {
    // FREEDOM not installed
   
    // actions not available
    popupInvisible('popupcard',$kdiv,'histo');
    popupInvisible('popupcard',$kdiv,'editstate');
    popupInvisible('popupcard',$kdiv,'revise');
    popupInvisible('popupcard',$kdiv,'editprof');
  }

  // ------------
  // add special views
  popupInvisible('popupcard',$kdiv,'sview');
  popupInvisible('popupcard',$kdiv,'sedit');

  if ($doc->cvid > 0 ) {

    $cvdoc = new Doc($doc->dbaccess, $doc->cvid);
    $cvdoc->set($doc);
    $ti = $cvdoc->getTValue("CV_IDVIEW");
    $tl = $cvdoc->getTValue("CV_LVIEW");
    $tz = $cvdoc->getTValue("CV_ZVIEW");
    $tk = $cvdoc->getTValue("CV_KVIEW");
    $tm = $cvdoc->getTValue("CV_MSKID");


  $tv=array(); // consult array views
  $te=array(); // edit array views
  if (count($tk) > 0)  {
    foreach ($tk as $k=>$v) {
      if ($tz[$k] != "") {
      
	if ($ti[$k]=="") $cvk="CV$k";
	else $cvk=$ti[$k];
	if ($v == "VEDIT") {
	  if (($clf)||($cud)) {	    
	    if ($cvdoc->control($cvk) == "") {
	      $te[$cvk] = array("idview"   => $cvk,
				"zoneview" => $tz[$k],
				"txtview"  => $tl[$k]);
	    }
	  }
	} else {      
	  if ($cvdoc->control($cvk) == "") {
	    $tv[$cvk] = array("idview"   => $cvk,
			      "zoneview" => $tz[$k],
			      "txtview"  => $tl[$k]);
	  }
	}
      }
    }
    $action->lay->SetBlockData("SVIEW",$tv);
    $action->lay->SetBlockData("SEDIT",$te);
  } 
  
  if (count($tv) > 0)  {
    popupInit('popupview',  array_keys($tv));
    foreach ($tv as $k=>$v)  popupActive('popupview',$kdiv,$k); 
    popupActive('popupcard',$kdiv,'sview');
  } else {
    popupInit('popupview',  array('z'));
  }
  if (count($te) > 0)  {
    popupInit('popupedit',  array_keys($te));
    foreach ($te as $k=>$v)  popupActive('popupedit',$kdiv,$k);  
    popupActive('popupcard',$kdiv,'sedit');
  } else {
    popupInit('popupedit',  array('z'));
  }
  }  




  popupGen($kdiv);
}