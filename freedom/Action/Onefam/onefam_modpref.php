<?php
// ---------------------------------------------------------------
// $Id: onefam_modpref.php,v 1.2 2002/10/02 09:12:29 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Onefam/onefam_modpref.php,v $
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

function onefam_modpref(&$action) 
{
  $tidsfam = GetHttpVars("idsfam"); // preferenced families
  $dbaccess = $action->GetParam("FREEDOM_DB");


  
  $idsfam = $action->GetParam("ONEFAM_IDS");
  $idsfam = implode(",",$tidsfam);

  $action->parent->param->Set("ONEFAM_IDS",$idsfam,PARAM_USER.$action->user->id,$action->parent->id);
	  
      
  redirect($action,GetHttpVars("app"),"ONEFAM_LIST");
  


}

?>
