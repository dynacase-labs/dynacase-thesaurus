<?php
// ---------------------------------------------------------------
// $Id: USERCARD.app,v 1.9 2004/05/13 16:17:15 eric Exp $
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
"childof"	=>"GENERIC"			//
);



$action_desc = array (
  
  array( 
   "name"		=>"USERCARD_VCARD",
   "short_name"		=>N_("view as vcard"),
   "acl"		=>"GENERIC_READ"
  ) ,
  array( 
   "name"		=>"USERCARD_INIT",
   "short_name"		=>N_("initialisation"),
   "acl"		=>"GENERIC_READ"
  )  ,
  array( 
   "name"		=>"USERCARD_IMPORTVCARD",
   "short_name"		=>N_("import vcard"),
   "layout"		=>"generic_import.xml",
   "acl"		=>"GENERIC_MASTER"
  )
                      );
   
?>
