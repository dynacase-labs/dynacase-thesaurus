<?php
// ---------------------------------------------------------------
// $Id: freedom_duplicate.php,v 1.1 2002/03/11 10:26:48 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_duplicate.php,v $
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
function freedom_duplicate(&$action) {
  // -----------------------------------

    // Get all the params      
  $dirid=GetHttpVars("dirid",10); // where to duplicate
  $docid=GetHttpVars("id",0);       // doc to duplicate
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $copy= new Doc($dbaccess, $docid);
 


  // test if doc with values
  $doc= new Doc($dbaccess, $docid);
  $values = $doc->getValues();
  if (! is_array($values)) $action->exitError(_("this kind of document cannot be duplicate"));


  // initiate a copy of the doc
  $copy->id = "";
  $copy->initid = "";
  $copy->revision = "0";
  $copy->title = _("duplication of")." ".$copy->title;
  $err = $copy->Add();

  
  if ($err != "") $action->exitError($err);
  

  //duplicate values 
    $value = new DocValue($dbaccess);
    $value->docid = $copy->id;
    
    
    while(list($k,$v) = each($values)) {
      $value->attrid = $v["attrid"];
      $value->value = $v["value"];
      $value->Add();
      
    }


  $copy->SetTitle($copy->title);
  // add to the same folder
  
  if (($dirid > 0) && ($copy->id > 0)) {
    $fld = new Dir($dbaccess, $dirid);

    
    $err = $fld->AddFile($copy->id);
    if ($err != "") {
      $copy->Delete();
      $action->exitError($err);
    }
    
  } 


  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");
  
}


?>
