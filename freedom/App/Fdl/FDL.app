<?php
// ---------------------------------------------------------------
// $Id: FDL.app,v 1.12 2003/04/14 17:02:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fdl/FDL.app,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2002
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

$app_desc = array (
"name"		=>"FDL",		//Name
"short_name"	=>N_("Freedoms lib"),		//Short name
"description"	=>N_("Freedoms library"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"displayable"	=>"N"			//Should be displayed on an app list (Y,N)

);

$app_acl = array (
  array(
   "name"		=>"NORMAL",
   "description"	=>N_("Access Action Library"),
   "group_default"       =>"Y"),
  array(
   "name"		=>"EDIT",
   "description"	=>N_("Access to edit action"),
   "group_default"       =>"N"),
  array(
   "name"		=>"EXPORT",
   "description"	=>N_("For export functions"),
   "group_default"       =>"N"),
  array(
   "name"		=>"FAMILY",
   "description"	=>N_("Manage families"),
   "group_default"       =>"N")
);

$action_desc = array (
  
  array( 
   "name"		=>"NONE",
   "short_name"		=>N_("nothing action"),
   "acl"		=>"NORMAL",
   "root"		=>"Y"
  ) ,
  array( 
   "name"		=>"ENUM_CHOICE",
   "short_name"		=>N_("to choose value from set"),
   "acl"		=>"NORMAL",
  ) ,
  array(
   "name"		=>"FREEDOM_INIT",
   "short_name"		=>N_("Freedom initialisation"),
   "acl"		=>"NORMAL"	
  ),
  array( 
   "name"		=>"EXPORTFLD",
   "short_name"		=>N_("export folder"),
   "acl"		=>"EXPORT"
  ) ,
  array( 
   "name"		=>"EXPORTFILE",
   "short_name"		=>N_("export file to consulting"),
   "acl"		=>"NORMAL"
  ) ,
  array( 
   "name"		=>"EXPORTFIRSTFILE",
   "short_name"		=>N_("export first file to consulting"),
   "acl"		=>"NORMAL",
   "script"		=>"exportfile.php",
   "function"		=>"exportfirstfile"
  ) ,
  array( 
   "name"		=>"MAILCARD",
   "short_name"		=>N_("send a document"),
   "acl"		=>"NORMAL"
  ) ,
  array( 
   "name"		=>"EDITMAIL",
   "short_name"		=>N_("edit mail"),
   "acl"		=>"NORMAL"
  )  ,
  array( 
   "name"		=>"CONFIRMMAIL",
   "short_name"		=>N_("confirm mail sended before change state"),
   "acl"		=>"NORMAL"
  ),
  array( 
   "name"		=>"MODACL",
   "short_name"		=>N_("modify acl"),
   "acl"		=>"NORMAL"
  ),
  array( 
   "name"		=>"VIEWSCARD",
   "short_name"		=>N_("view standalone card"),
   "acl"		=>"NORMAL"
  ) ,
  array( 
   "name"		=>"FDL_CARD",
   "short_name"		=>N_("view card"),
   "acl"		=>"NORMAL"
  ) ,

  array( 
   "name"		=>"LOCKFILE",
   "short_name"		=>N_("lock file to edit"),
   "acl"		=>"EDIT"
  ) ,

  array( 
   "name"		=>"VIEWXML",
   "short_name"		=>N_("view xml"),
   "acl"		=>"NORMAL"
  ) ,

  array( 
   "name"		=>"UNLOCKFILE",
   "short_name"		=>N_("unlock file to discard edit"),
   "acl"		=>"EDIT"
  ) ,
  array( 
   "name"		=>"WORKFLOW_INIT",
   "short_name"		=>N_("init workflow profile attributes"),
   "acl"		=>"NORMAL"
  ) 
                      );	
   
?>
