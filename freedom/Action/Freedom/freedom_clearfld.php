<?php
// ---------------------------------------------------------------
// $Id: freedom_clearfld.php,v 1.1 2003/07/24 13:12:53 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_clearfld.php,v $
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

// ==========================================================================
// unreference all document in the folder
// ==========================================================================

include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function freedom_clearfld(&$action) {
  // -----------------------------------

  // insert the documents of $dirid in folder $id
    //    PrintAllHttpVars();

  // Get all the params      
  $docid=GetHttpVars("id");
  $mode=GetHttpVars("mode","latest");
  $return=GetHttpVars("return"); // return action may be folio


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc= new Doc($dbaccess, $docid);
  $err=$doc->Clear();

  if ($err != "") $action->exitError($err);
  
  
  redirect($action,"FREEDOM","FREEDOM_VIEW&dirid=$docid");
}




?>
