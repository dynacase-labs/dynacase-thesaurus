<?php
// ---------------------------------------------------------------
// $Id: generic_del.php,v 1.7 2003/04/08 12:44:10 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_del.php,v $
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
include_once("FDL/Class.DocAttr.php");
include_once("FDL/freedom_util.php");

// -----------------------------------
function generic_del(&$action) {
// -----------------------------------


  // Get all the params      
  $docid=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
   
  if ( $docid > 0 ) {



    $doc = new Doc($dbaccess, $docid);
  
    // must unlocked before
    $err=$doc->CanLockFile();
    if ($err != "")  $action-> ExitError($err);

    // ------------------------------
    // delete document
     $err=$doc-> Delete();
     if ($err != "")  $action-> ExitError($err);
     redirect($action,"FDL","FDL_CARD&id=$docid");
      
  }

  
  redirect($action,GetHttpVars("app"),"GENERIC_LOGO");

}
?>
