<?php
// ---------------------------------------------------------------
// $Id: generic_duplicate.php,v 1.3 2003/01/30 09:38:36 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_duplicate.php,v $
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


include_once("FDL/duplicate.php");

include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php"); 


// -----------------------------------
function generic_duplicate(&$action) {
  // -----------------------------------

    // Get all the params      
  $dirid=GetHttpVars("dirid", getDefFld($action)); // where to duplicate
  $docid=GetHttpVars("id",0);       // doc to duplicate


  
  $copy=duplicate($action, $dirid, $docid);

  // add to default catg (also)
  if (getDefFld($action) !=  $dirid) {

    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fld = new Dir( $dbaccess, getDefFld($action) );
      
    $err = $fld->AddFile($copy->id);
    if ($err != "") {
      $action->exitError($err);
    }
  }
  redirect($action,"FDL","FDL_CARD&id=".$copy->id, $action->GetParam("CORE_STANDURL"));
  
}


?>
