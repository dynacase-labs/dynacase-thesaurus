<?php
// ---------------------------------------------------------------
// $Id: ONEFAM.app,v 1.1 2002/08/28 09:39:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Onefam/ONEFAM.app,v $
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
"name"		=>"ONEFAM",		//Name
"short_name"	=>N_("Onefam"),		//Short name
"description"	=>N_("One Familly Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"onefam.gif",		//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>""			//
);

$action_desc = array (
  array( 
   "name"		=>"ONEFAM_ROOT",
   "short_name"		=>N_("one familly root"),
   "acl"		=>"ONEFAM_READ",
   "root"		=>"Y"
  )  ,
  array( 
   "name"		=>"ONEFAM_LIST",
   "short_name"		=>N_("familly list"),
   "acl"		=>"ONEFAM_READ"
  )  ,
  array( 
   "name"		=>"ONEFAM_TOGEN",
   "short_name"		=>N_("redirect to generic"),
   "acl"		=>"ONEFAM_READ"
  )  ,
  array( 
   "name"		=>"ONEFAM_LOGO",
   "short_name"		=>N_("familly result"),
   "acl"		=>"ONEFAM_READ"
  ) 
);

$app_acl = array (
  
  array(
   "name"               =>"ONEFAM_MASTER",
   "description"        =>N_("Access Onefam Master Management")),
  array(
   "name"               =>"ONEFAM",
   "description"        =>N_("Access To Onefam Management"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"ONEFAM_READ",
   "description"        =>N_("Access To Read Card"),
   "group_default"       =>"Y")
);
?>
