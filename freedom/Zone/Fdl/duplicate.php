<?php
// ---------------------------------------------------------------
// $Id: duplicate.php,v 1.2 2002/09/16 14:42:10 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/duplicate.php,v $
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




include_once("FDL/Class.Dir.php");


// -----------------------------------
function duplicate(&$action, $dirid, $docid) {
  // -----------------------------------

  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $copy= new Doc($dbaccess, $docid);
 


  // test if doc with values
  $doc= new Doc($dbaccess, $docid);
  $cdoc= new Doc($dbaccess, $doc->fromid);
  $values = $doc->getValues();
  if (! is_array($values)) $action->exitError(_("this kind of document cannot be duplicate"));


  // initiate a copy of the doc
  $copy->id = "";
  $copy->initid = "";
  $copy->revision = "0";
  $copy->locked = "0";
  $copy->state = "";
  $copy->profid = $cdoc->cprofid;;
  $copy->title = _("duplication of")." ".$copy->title;
  $err = $copy->Add();

  
  if ($err != "") $action->exitError($err);
  

  //duplicate values 
    $value = new DocValue($dbaccess);
    $value->docid = $copy->id;
    
    
    while(list($k,$v) = each($values)) {
      $value->attrid = $k;
      $value->value = $v;
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


  $action->AddLogMsg(sprintf(_("new duplicate document is named : %s"),$copy->title));

  return $copy;
  
}


?>
