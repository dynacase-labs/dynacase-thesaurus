<?php
// ---------------------------------------------------------------
// $Id: FUSERS.app,v 1.2 2004/08/12 10:24:27 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Fusers/FUSERS.app,v $
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
"name"		=>"FUSERS",		//Name
"short_name"	=>N_("Users"),		//Short name
"description"	=>N_("Users Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"fusers.gif",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"childof"	=>"ONEFAM"			//
);

$action_desc = array (
		      array( 
			    "name"		=>"FUSERS_ADMIN",
			    "short_name"		=>N_("administrative tools"),
			    "acl"		=>"ONEFAM_MASTER"
			    ),
		      array( 
			    "name"		=>"FUSERS_IUSER",
			    "short_name"		=>N_("refresh users intranet attributes"),
			    "acl"		=>"ONEFAM_MASTER"
			    ),
		      array( 
			    "name"		=>"FUSERS_LDAPINIT",
			    "short_name"		=>N_("refresh ldap entries"),
			    "script"           => "fusers_iuser.php",
			    "function"           =>"fusers_ldapinit",
			    "acl"		=>"ONEFAM_MASTER"
			    )
		      );
   
?>
