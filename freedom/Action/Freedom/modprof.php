<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: modprof.php,v 1.16 2007/07/27 15:13:54 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: modprof.php,v 1.16 2007/07/27 15:13:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/modprof.php,v $
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
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function modprof(&$action) {
  // -----------------------------------
    
    
    
  // Get all the params      
  $docid=GetHttpVars("docid");
  $createp = GetHttpVars("create",0); // 1 if use for create profile (only for familly)
  $profid = GetHttpVars("profid");  
  $cvid = GetHttpVars("cvid");  
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
  $doc = new_Doc($dbaccess,$docid);

  // control modify acl
  $err= $doc->Control("modifyacl");
  if ($err != "")    $action-> ExitError($err);

  $err= $doc -> lock(true); // auto lock
  if ($err != "")    $action-> ExitError($err);
  
  
  // test object permission before modify values (no access control on values yet)
  $err=$doc-> CanUpdateDoc();
  if ($err != "")    $action-> ExitError($err);
  

  if ($createp) {
    // change creation profile
    if ( $doc->cprofid != $profid) {
      $doc->AddComment(sprintf(_("Change creation profil to %s [%d]"),$doc->getTitle($profid),$profid));
      $doc->cprofid = $profid; // new creation profile access
    }
    if ($doc->ccvid != $cvid) {
      $doc->ccvid = $cvid; //  default control view for creation
      $doc->AddComment(sprintf(_("Change creation view control to %s [%d]"),$doc->getTitle($cvid),$cvid));
    }
  } else {

    if (($doc->profid == $doc->id) && ($profid == 0)) {
      // unset control
      $doc->UnsetControl();
    }  
    if ($doc->profid != $profid)    $doc->AddComment(sprintf(_("Change profil to %s [%d]"),$doc->getTitle($profid),$profid));
    if ($doc->cvid != $cvid)    $doc->AddComment(sprintf(_("Change view control  to %s [%d]"),$doc->getTitle($cvid),$cvid));
    $doc->setProfil($profid);// change profile
    $doc->setCvid($cvid);// change view control
    
    // specific control
    if ($doc->profid == $doc->id)    $doc->SetControl();
  
    $doc->disableEditControl(); // need because new profil is not enable yet
  }
  $err= $doc-> Modify();
  
  if ( $err != "" ) $action->exitError($err);
  
  
  
  $doc -> unlock(true); // auto unlock
  
  
  
  
  redirect($action,"FDL","FDL_CARD&props=Y&id=$docid",
	   $action->GetParam("CORE_STANDURL"));
}




?>
