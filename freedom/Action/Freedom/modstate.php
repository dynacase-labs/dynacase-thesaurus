<?php
// ---------------------------------------------------------------
// $Id: modstate.php,v 1.1 2002/09/16 14:42:09 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/modstate.php,v $
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
include_once("FDL/modcard.php");



// -----------------------------------
function modstate(&$action) {
  // -----------------------------------
    
    
    
    // Get all the params      
      $docid=GetHttpVars("id");
  $state = GetHttpVars("newstate"); // new state
  $comment = GetHttpVars("comment"); // comment
  $force = (GetHttpVars("fstate","no")=="yes"); // force change

    
  if ( $docid == 0 ) $action->exitError(_("the document is not referenced: cannot apply state modification"));
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  
  
  // initialise object
    $doc = new Doc($dbaccess,$docid);
  
  


  
 
  if ($doc->wid > 0) {
    $wdoc = new Doc($dbaccess,$doc->wid);
    $wdoc->Set($doc);
    $err=$wdoc->ChangeState($state,$comment,$force);
    if ($err != "")  $action-> ExitError($err);
  } else {
    $action->AddLogMsg(sprintf(_("the document %s is not related to a workflow"),$doc->title));
  }
  
  
  
  
  
  
  
  
  
    redirect($action,GetHttpVars("redirect_app",GetHttpVars("app")),
	     GetHttpVars("redirect_act","FREEDOM_CARD&id=".$doc->id));
  
  
  
  
}




?>
