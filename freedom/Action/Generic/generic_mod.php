<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_mod.php,v 1.20 2003/10/09 12:08:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_mod.php,v 1.20 2003/10/09 12:08:42 eric Exp $
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
  $retedit=GetHttpVars("retedit","N")=="Y"; // true  if return need edition
  

  $dbaccess = $action->GetParam("FREEDOM_DB");

  
  $err = modcard($action, $ndocid); // ndocid change if new doc


  if ($err != "")  $action->AddWarningMsg($err);
  else {   
  
    $doc= new Doc($dbaccess, $ndocid);
    if ($docid > 0) AddLogMsg(sprintf(_("%s has been modified"),$doc->title));

    if ($docid == 0) { // new file => add in a folder
   
      AddLogMsg(sprintf(_("%s has been created"),$doc->title));
   
      $cdoc = $doc->getFamDoc();
      if ($cdoc->dfldid>0)  $dirid=$cdoc->dfldid;
    

      if ($dirid > 0) {
	$fld = new Doc($dbaccess, $dirid);
       
	$err=$fld->AddFile($doc->id);
	if ($err != "") $action->AddLogMsg($err);
      }
    
   
    
    } 
  }
  
  if ($retedit) {
    redirect($action,GetHttpVars("redirect_app","GENERIC"),
	     GetHttpVars("redirect_act","GENERIC_EDIT&id=$ndocid"),
	     $action->GetParam("CORE_STANDURL"));
  } else {
  
    // $action->register("reload$ndocid","Y"); // to reload cached client file
    redirect($action,GetHttpVars("redirect_app","FDL"),
	     GetHttpVars("redirect_act","FDL_CARD&refreshfld=Y&id=$ndocid"),
	     $action->GetParam("CORE_STANDURL"));
  }
  
}


?>
