<?php
// ---------------------------------------------------------------
// $Id: freedom_mod.php,v 1.15 2003/01/24 14:10:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_mod.php,v $
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
include_once("FDL/Class.DocFam.php");


// -----------------------------------
function freedom_mod(&$action) {
  // -----------------------------------
    
    // Get all the params      
      $dirid=GetHttpVars("dirid",0);
  $docid=GetHttpVars("id",0); 
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  
  $err = modcard($action, $ndocid); // ndocid change if new doc
    if ($err != "")  $action-> ExitError($err);
  
  
  $doc= new Doc($dbaccess, $ndocid);
  $action->AddLogMsg(sprintf(_("%s has been modified"),$doc->title));

  
  if  ($docid == 0) {
    if ($dirid > 0) {
      $fld = new Doc($dbaccess, $dirid);    
      if ($fld->doctype != 'D') $dirid=0;
    }
    if ($dirid == 0) {
      $cdoc = new DocFam($dbaccess, $doc->fromid);
      if ($cdoc->dfldid>0)  $fld = new Doc($dbaccess,$cdoc->dfldid);
      else {
	$fld = new Doc($dbaccess,UNCLASS_FLD);
	$home = $fld->getHome();
      
	if ($home->id > 0) $fld = $home;
      }
    }
  
    $fld->AddFile($doc->id);   
  } 
  
  
  
  
  
  // $action->register("reload$ndocid","Y"); // to reload cached client file
  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&refreshfld=Y&id=$ndocid"),
	   $action->GetParam("CORE_STANDURL"));
  
}


?>
