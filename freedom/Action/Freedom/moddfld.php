<?php
// ---------------------------------------------------------------
// $Id: moddfld.php,v 1.3 2003/01/21 15:43:35 eric Exp $
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
  $current = (GetHttpVars("current","N")=="Y");
  $fldid=  GetHttpVars("dfldid");
    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply profile access modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
  $doc = new Doc($dbaccess,$docid);
  if ($current) $doc->cfldid = $fldid; 
  else $doc->dfldid = $fldid; // new default folder
  
  
  // test object permission before modify values (no access control on values yet)
  $doc->lock(true); // enabled autolock
  $err=$doc-> CanUpdateDoc();
  if ($err != "") $action-> ExitError($err);
  
  $doc-> Modify();
  
  
  $doc->unlock(true); // disabled autolock
  
  
  
  redirect($action,"FDL","FDL_CARD&id=$docid",$action->GetParam("CORE_STANDURL"));
}




?>
