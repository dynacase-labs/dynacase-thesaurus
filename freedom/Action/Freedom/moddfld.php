<?php
// ---------------------------------------------------------------
// $Id: moddfld.php,v 1.1 2002/08/28 09:39:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/moddfld.php,v $
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
function moddfld(&$action) {
  // -----------------------------------
    
    
    
    // Get all the params      
      $docid=GetHttpVars("docid");
  $createp = GetHttpVars("create",0); // 1 if use for create profile (only for familly)
    
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
    $ofreedom = new Doc($dbaccess,$docid);
      $ofreedom->dfldid = GetHttpVars("dfldid"); // new profile access
  
  
  // test object permission before modify values (no access control on values yet)
    $err=$ofreedom-> CanUpdateDoc();
  if ($err != "") $action-> ExitError($err);
  
  $ofreedom-> Modify();
  
  
  
  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_CARD&id=$docid");
}




?>
