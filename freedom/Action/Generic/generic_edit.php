<?php

// ---------------------------------------------------------------
// $Id: generic_edit.php,v 1.6 2002/09/02 16:38:49 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_edit.php,v $
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

include_once("Class.QueryDb.php");
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_edit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = GetHttpVars("id",0);        // document to edit
  $famid = GetHttpVars("classid",getDefFam($action)); // use when new doc or change class

  $dirid = GetHttpVars("dirid",0); // directory to place doc if new doc




  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
   


 



 

  if ($docid == 0)
    {
      $action->lay->Set("TITLE", _("new usercard"));
      $action->lay->Set("editaction", $action->text("create"));
      $doc= createDoc($dbaccess,$famid);
    }
  else
    {    

      $doc= new Doc ($dbaccess,$docid);
      $err = $doc->CanLockFile();
      if ($err != "")   $action->ExitError($err);

      $famid = $doc->fromid;
      if (! $doc->isAffected()) $action->ExitError(_("document not referenced"));
  

      $action->lay->Set("TITLE", $doc->title);
      $action->lay->Set("editaction", $action->text("modify"));
    }
    

 

  

  $action->lay->Set("id", $docid);
  $action->lay->Set("dirid", $dirid);


 
  // information propagation
  $action->lay->Set("classid", $famid);
  $action->lay->Set("dirid", $dirid);
  $action->lay->Set("id", $docid);
    

}
?>
