<?php
// ---------------------------------------------------------------
// $Id: generic_addcatg.php,v 1.5 2003/01/20 19:09:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_addcatg.php,v $
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
include_once("GENERIC/generic_util.php"); 


// -----------------------------------
function generic_addcatg(&$action) {
  // -----------------------------------

  // Get all the params      
   $dirid=GetHttpVars("dirid", getDefFld($action));
//   $newcatg=GetHttpVars("newcatg"); 

//   if ($newcatg == "") $action->exitError(_("the title of the new category cannot be empty"));
  


  $dbaccess = $action->GetParam("FREEDOM_DB");

  
      
  $err = modcard($action, $ndocid); // ndocid change if new doc

  if ($err != "")  $action-> ExitError($err);
  

  

  if ($dirid > 0)  {
    $fld = new Dir($dbaccess, $dirid);

    $doc= new Doc($dbaccess, $ndocid);
    
    $fld->AddFile($doc->id);
    
  } 
  redirect($action,"FDL","FDL_CARD&id=$ndocid");
  
}


?>
