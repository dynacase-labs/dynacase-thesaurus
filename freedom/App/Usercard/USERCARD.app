<?php
// ---------------------------------------------------------------
// $Id: USERCARD.app,v 1.2 2002/02/14 18:11:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Usercard/USERCARD.app,v $
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
$app_desc = array (
"name"		=>"USERCARD",		//Name
"short_name"	=>N_("User Card"),		//Short name
"description"	=>N_("User Card Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"usercard.gif",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>""			//
);

$app_acl = array (
  
  array(
   "name"               =>"USERCARD_MASTER",
   "description"        =>N_("Access Usercard Master Management")),
  array(
   "name"               =>"USERCARD",
   "description"        =>N_("Access To Usercard Management"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"USERCARD_READ",
   "description"        =>N_("Access To Read User Card"),
   "group_default"       =>"Y")
);

$action_desc = array (
  array( 
   "name"		=>"USERCARD_ROOT",
   "short_name"		=>N_("incident home page"),
   "acl"		=>"USERCARD",
   "root"		=>"Y"
  ) ,
  array( 
   "name"		=>"USERCARD_EDIT",
   "short_name"		=>N_("edition"),
   "acl"		=>"USERCARD"
  ) ,
  array( 
   "name"		=>"USERCARD_ADDCATG",
   "short_name"		=>N_("add category"),
   "acl"		=>"USERCARD_MASTER"
  ) ,
  array( 
   "name"		=>"USERCARD_EDITNEWCATG",
   "short_name"		=>N_("edit to add category"),
   "acl"		=>"USERCARD_MASTER"
  ) ,
  array( 
   "name"		=>"USERCARD_EDITCHANGECATG",
   "short_name"		=>N_("edit to change category"),
   "acl"		=>"USERCARD"
  ) ,
  array( 
   "name"		=>"USERCARD_CHANGECATG",
   "short_name"		=>N_("change category"),
   "acl"		=>"USERCARD"
  ) ,
  array( 
   "name"		=>"HISTO",
   "short_name"		=>N_("document history"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_MOD",
   "short_name"		=>N_("modification or creation"),
   "acl"		=>"USERCARD"
  ) ,
  array( 
   "name"		=>"USERCARD_VCARD",
   "short_name"		=>N_("view as vcard"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_LOGO",
   "short_name"		=>N_("display logo"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"UNLOCKFILE",
   "short_name"		=>N_("unlock incident file"),
   "acl"		=>"USERCARD"
  ) ,
  array( 
   "name"		=>"USERCARD_LIST",
   "short_name"		=>N_("view list"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_CARD",
   "short_name"		=>N_("view an incident"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_EDITIMPORT",
   "short_name"		=>N_("edit import vcard"),
   "acl"		=>"USERCARD_MASTER"
  ) ,
  array( 
   "name"		=>"USERCARD_TAB",
   "short_name"		=>N_("view a part of list"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_IMPORTVCARD",
   "short_name"		=>N_("import vcard"),
   "acl"		=>"USERCARD_MASTER"
  ) ,
  array( 
   "name"		=>"USERCARD_SEARCH",
   "short_name"		=>N_("search an incident"),
   "acl"		=>"USERCARD_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_BARMENU",
   "short_name"		=>N_("bar menu"),
   "acl"		=>"USERCARD"
  ) ,
  array( 
   "name"		=>"USERCARD_INIT",
   "short_name"		=>N_("initialisation"),
   "acl"		=>"USERCARD"
  ) 
                      );
   
?>
