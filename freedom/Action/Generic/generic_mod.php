<?php
// ---------------------------------------------------------------
// $Id: generic_mod.php,v 1.13 2003/01/20 19:09:28 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_mod.php,v $
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

include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.Dir.php");


// -----------------------------------
function generic_mod(&$action) {
  // -----------------------------------

  // Get all the params      
  $dirid=GetHttpVars("dirid",0);
  $docid=GetHttpVars("id",0); 
  $catgid=GetHttpVars("catgid",0); 
  

  $dbaccess = $action->GetParam("FREEDOM_DB");

  
  $err = modcard($action, $ndocid); // ndocid change if new doc


  if ($err != "")  $action-> ExitError($err);
      
  
  $doc= new Doc($dbaccess, $ndocid);
  AddLogMsg(sprintf(_("%s has been modified"),$doc->title));

  if ($docid == 0) { // new file => add in a folder
   
    if ($dirid == 0) {      
      $cdoc = new DocFam($dbaccess, $doc->fromid);
      if ($cdoc->dfldid>0)  $dirid=$cdoc->dfldid;
    }

    if ($dirid > 0) {
       $fld = new Dir($dbaccess, $dirid);
       
       $doc= new Doc($dbaccess, $ndocid);
    
       $fld->AddFile($doc->id);
     }
    
    if ($catgid > 0) {
      //add to new default catg 
	$fld = new Dir($dbaccess, $catgid);
      $fld->AddFile($doc->id);
	
    }
    
  } 

  
  $action->register("reload$ndocid","Y");// to reload cached client file
  redirect($action,GetHttpVars("redirect_app","FDL"),
	   GetHttpVars("redirect_act","FDL_CARD&id=$ndocid"));
  
}


?>
