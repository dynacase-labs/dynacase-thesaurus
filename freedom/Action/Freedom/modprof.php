<?php
// ---------------------------------------------------------------
// $Id: modprof.php,v 1.6 2002/11/13 15:49:36 eric Exp $
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
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
  $doc = new Doc($dbaccess,$docid);
  $err= $doc -> lock(true); // auto lock
  if ($err != "")    $action-> ExitError($err);
  
  
  // test object permission before modify values (no access control on values yet)
  $err=$doc-> CanUpdateDoc();
  if ($err != "")    $action-> ExitError($err);
  


  if (($doc->profid == $doc->id) && ($profid == 0)) {
    // unset control
    $doc->UnsetControl();
  }
  
  if (($profid > 0) && ($profid != $doc->id)) {
    // make sure that the profil is activated
    $pdoc=new Doc($dbaccess, $profid);
    if ($pdoc->profil == 0) $profid = -$profid; // inhibition
  }

  if ($createp) {
    // change creation profile
    $doc->cprofid = $profid; // new creation profile access
  } else {
    // change profile
    $doc->profid = $profid; // new profile access
  }
  
  
  
  
  // specific control
  if ($doc->profid == $doc->id)    $doc->SetControl();
  
  $doc->disableEditControl(); // need because new profil is not enable yet
  $err= $doc-> Modify();
  
  if ( $err != "" ) $action->exitError($err);
  
  
  
  
  $doc -> unlock(true); // auto unlock
  
  
  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_CARD&id=$docid");
}




?>
