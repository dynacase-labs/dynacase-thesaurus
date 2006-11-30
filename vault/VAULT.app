<?php
// ---------------------------------------------------------------
// $Id: VAULT.app,v 1.4 2006/11/30 17:40:09 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/VAULT.app,v $
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
// $Log: VAULT.app,v $
// Revision 1.4  2006/11/30 17:40:09  eric
// new initialisation
//
// Revision 1.3  2005/11/15 13:05:41  eric
// application order
//
// Revision 1.2  2005/07/29 08:15:31  eric
// add icon
//
// Revision 1.1  2001/11/16 09:57:01  marc
// V0_0_1 Initial release, see CHANGELOG
//
// ---------------------------------------------------------------
$app_desc = array (
"name"		=>"VAULT",		//Name
"short_name"	=>N_("Vault"),		//Short name
"description"	=>N_("Vault Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"vault.gif",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y",			//Use multiframe ? (Y,N)
"iorder"        =>140
);

$app_acl = array (
  array(
   "name"		=>"VAULT_MASTER",
   "description"	=>N_("Vault manager"),
   "admin"		=>TRUE),
  array(
   "name"               =>"VAULT_USER",
   "description"        =>N_("Vault user"),
   "group_default"       =>"Y")
);

$action_desc = array (
  array( 
   "name"		=>"VAULT_VIEW",
   "short_name"		=>N_("analyze vaults occupation"),
   "acl"		=>"VAULT_MASTER",
   "root"		=>"Y"
  ),
  array( 
   "name"		=>"VAULT_CREATEFS",
   "short_name"		=>N_("create new vault"),
   "acl"		=>"VAULT_MASTER"
  )
                      );
   
?>
