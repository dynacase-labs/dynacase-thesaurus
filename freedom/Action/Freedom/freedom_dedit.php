<?php

// ---------------------------------------------------------------
// $Id: freedom_dedit.php,v 1.4 2003/05/23 15:30:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_dedit.php,v $
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

include_once("FDL/freedom_util.php");
include_once("FDL/Lib.Dir.php");

// -----------------------------------
function freedom_dedit(&$action) {
  // -----------------------------------

  // Get All Parameters
  $docid = 0;//GetHttpVars("id",0);        // document to edit
  $classid = GetHttpVars("classid",0); // use when new doc or change class




  // Set the globals elements
  $dbaccess = $action->GetParam("FREEDOM_DB");
   if ($docid > 0) {

     $doc= new Doc($dbaccess, $docid);

     if (!$doc->isAlive()) {
       // the doesn't exist
       $docid=0; // to recreate a new one
     }
   }

  if ($docid == 0) {
    // create default if needed
    $doc = createDoc($dbaccess, $classid);
    $fdoc= new DocFam($dbaccess, $classid);

    $doc->usefor='D'; // default document
    $doc->profid=$fdoc->profid; // same profil as familly doc
    $doc->title=sprintf(_("default values for %s"),$fdoc->title);
    $doc->setDefaultValues($fdoc->defval);
    $err=$doc->Add();

    if ($err != "") $action->exitError($err);
    $docid= $doc->id;

    // insert them if its family
    $fdoc= new DocFam($dbaccess, $classid);
    $fdoc->ddocid=$docid;
    $err=$fdoc->modify();
    if ($err != "") $action->exitError($err);

    
  }


 
 
  redirect($action,GetHttpVars("app"), "FREEDOM_EDIT&id=$docid");
    

}
?>
