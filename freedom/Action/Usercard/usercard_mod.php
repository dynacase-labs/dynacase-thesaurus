<?php
// ---------------------------------------------------------------
// $Id: usercard_mod.php,v 1.3 2002/03/12 09:55:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Usercard/Attic/usercard_mod.php,v $
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


include_once("FDL/modcard.php");

include_once("FDL/Class.Dir.php");


// -----------------------------------
function usercard_mod(&$action) {
  // -----------------------------------

  // Get all the params      
  $dirid=GetHttpVars("dirid",0);
  $docid=GetHttpVars("id",0); 
  

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid > 0) {
    
      $doc= new DocUser ($dbaccess,$docid);
      // lock the doc if not before modify
      $err = $doc->lock();
      if ($err != "")   $action->ExitError($err);
  }
  $err = modcard($action, $ndocid); // ndocid change if new doc

  if ($err != "")  $action-> ExitError($err);
      
  

  if (($dirid > 0) && ($docid == 0)) {
    $fld = new Dir($dbaccess, $dirid);

    $doc= new Doc($dbaccess, $ndocid);
    
    $fld->AddFile($doc->id);
    
  } else {
    $doc= new Doc($dbaccess, $docid);
  }


  $err = $doc->PostModify(); // compute new lock & LDAP
  if ($err != "")  $action-> ExitError($err);
  
  
  redirect($action,GetHttpVars("app"),"USERCARD_CARD&id=$ndocid");
  
}


?>
