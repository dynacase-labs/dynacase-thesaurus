<?php
// ---------------------------------------------------------------
// $Id: FREEDOM.app,v 1.1 2002/02/05 16:34:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/App/Freedom/FREEDOM.app,v $
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
// $Log: FREEDOM.app,v $
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.12  2002/01/04 15:08:04  eric
// modif pour init
//
// Revision 1.11  2001/12/18 09:18:10  eric
// first API with ZONE
//
// Revision 1.10  2001/12/08 17:16:30  eric
// evolution des attributs
//
// Revision 1.9  2001/11/28 13:40:10  eric
// home directory
//
// Revision 1.8  2001/11/22 17:49:12  eric
// search doc
//
// Revision 1.7  2001/11/21 14:28:19  eric
// double click : first file export
//
// Revision 1.6  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
//
// Revision 1.5  2001/11/19 18:04:22  eric
// aspect change
//
// Revision 1.4  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.3  2001/11/14 15:31:03  eric
// optimisation & divers...
//
// Revision 1.2  2001/11/09 18:54:21  eric
// et un de plus
//
// Revision 1.1  2001/11/09 09:35:47  eric
// gestion documentaire
//
// Revision 1.10  2001/10/17 14:35:55  eric
// mise en place de i18n via gettext
//
// Revision 1.9  2001/09/10 16:51:45  eric
// ajout accessibilté objet
//
// Revision 1.8  2001/09/05 17:13:19  eric
// changement user_default par group_default
//
// Revision 1.7  2001/08/31 13:30:51  eric
// modif pour accessibilité
//
// Revision 1.6  2001/07/26 10:45:17  eric
// droit par défaut FREEDOM
//
// Revision 1.5  2001/07/26 09:40:42  eric
// correction noms acl
//
// Revision 1.4  2001/07/05 11:41:31  eric
// ajout export format vcard
//
// Revision 1.3  2001/06/19 15:58:01  eric
// fonctions d'importation
//
// Revision 1.2  2001/06/15 10:21:54  eric
// typage des attributs avec ajout image
//
// Revision 1.1  2001/06/13 14:32:01  eric
// freedom address book
//
// ---------------------------------------------------------------
$app_desc = array (
"name"		=>"FREEDOM",		//Name
"short_name"	=>N_("Freedoms"),		//Short name
"description"	=>N_("Freedoms Management"),//long description
"access_free"	=>"N",			//Access free ? (Y,N)
"icon"		=>"freedom.gif",	//Icon
"displayable"	=>"Y",			//Should be displayed on an app list (Y,N)
"with_frame"	=>"Y"			//Use multiframe ? (Y,N)
);

$app_acl = array (
  array(
   "name"		=>"ADMIN",
   "description"	=>N_("Access To All Users"),
   "admin"		=>TRUE),
  array(
   "name"               =>"FREEDOM_MASTER",
   "description"        =>N_("Access Management Database")),
  array(
   "name"               =>"FREEDOM",
   "description"        =>N_("Access To My Own account"),
   "group_default"       =>"Y"),
  array(
   "name"               =>"FREEDOM_READ",
   "description"        =>N_("Access To Read Only"),
   "group_default"       =>"Y")
);

$action_desc = array (
  array( 
   "name"		=>"FREEDOM_FRAME",
   "short_name"		=>N_("Freedoms frame"),
   "acl"		=>"FREEDOM_READ",
   "root"		=>"Y"
  ) ,
  array( 
   "name"		=>"FREEDOM_LIST",
   "short_name"		=>N_("Freedoms list"),
   "acl"		=>"FREEDOM_READ",
  ) ,
  array( 
   "name"		=>"ENUM_CHOICE",
   "short_name"		=>N_("to choose value from set"),
   "acl"		=>"FREEDOM",
  ) ,
  array( 
   "name"		=>"FREEDOM_IMPORT",
   "short_name"		=>N_("query document import"),
   "acl"		=>"FREEDOM_MASTER",
  ) ,
  array( 
   "name"		=>"ADDIMPORTFILE",
   "short_name"		=>N_("add document import"),
   "acl"		=>"FREEDOM_MASTER",
   "script"		=>"freedom_import.php",
   "function"		=>"add_import_file",
   "layout"		=>"freedom_import.xml"
  ) ,
  array( 
   "name"		=>"FREEDOM_VIEW",
   "short_name"		=>N_("Freedoms view folder"),
   "layout"		=>"freedom_list.xml",
   "acl"		=>"FREEDOM_READ",
  ) ,
  array( 
   "name"		=>"POPUP",
   "short_name"		=>N_("popup menu"),
   "acl"		=>"FREEDOM_READ",
  ) ,
  array( 
   "name"		=>"ADDDIRFILE",
   "short_name"		=>N_("add file query into directory"),
   "acl"		=>"FREEDOM",
  ) ,
  array( 
   "name"		=>"DELDIRFILE",
   "short_name"		=>N_("delete file query into directory"),
   "acl"		=>"FREEDOM",
  ) ,
  array( 
   "name"		=>"FOLDERS",
   "short_name"		=>N_("folder tree"),
   "acl"		=>"FREEDOM_READ",
  ) ,
  array( 
   "name"		=>"FREEDOM_ICONS",
   "toc"		=>"Y",
   "short_name"		=>N_("Freedoms icon list"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"BARMENU",
   "short_name"		=>N_("bar menu"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"FREEDOM_CARD",
   "short_name"		=>N_("Freedoms card"),
   "acl"		=>"FREEDOM_READ",
  ) ,
  array( 
   "name"		=>"DEFATTR",
   "short_name"		=>N_("attributes definitions"),
   "acl"		=>"FREEDOM",
  ) ,
  array( 
   "name"		=>"REFRESHDIR",
   "short_name"		=>N_("refresh directory"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MODATTR",
   "short_name"		=>N_("attributes modification"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"LOCKFILE",
   "short_name"		=>N_("lock file to edit"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"EDITPROF",
   "short_name"		=>N_("edit profile access"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"MODPROF",
   "short_name"		=>N_("change profile access"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"CTRLDOC",
   "short_name"		=>N_("set the document controlled"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"UNLOCKFILE",
   "short_name"		=>N_("abord edition"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"CHANGEICON",
   "short_name"		=>N_("change icon document"),
   "acl"		=>"FREEDOM"
  ) ,
  array( 
   "name"		=>"SEARCH",
   "short_name"		=>N_("search document"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"EDIT_SEARCH",
   "short_name"		=>N_("search document criteria"),
   "acl"		=>"FREEDOM_READ"
  ) ,
  array( 
   "name"		=>"QUERYFILE",
   "short_name"		=>N_("ask for a new file revision"),
   "acl"		=>"FREEDOM"
  ) ,
  array(
   "name"               =>"GENCSS",
   "layout"		=>"freedom.css"
  ),
  array(
   "name"		=>"FREEDOM_EDIT",
   "short_name"		=>N_("edit document properties"),
   "acl"		=>"FREEDOM"
  ),
  array( 
   "name"		=>"REVCOMMENT",
   "short_name"		=>N_("add comment before revise document"),
   "acl"		=>"FREEDOM"
  ) ,
  array(
   "name"		=>"REVISION",
   "short_name"		=>N_("make a new document revision"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"HISTO",
   "short_name"		=>N_("view history revision"),
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"GENCSS",
   "short_name"		=>N_("style sheet"),
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"POPUPCARD",
   "short_name"		=>N_("widget display popup for a description card"),
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"FREEDOM_LOGO",
   "acl"		=>"FREEDOM_READ"
  ),
  array(
   "name"		=>"FREEDOM_MOD",
   "short_name"		=>N_("Freedom modification"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"FREEDOM_DEL",
   "short_name"		=>N_("Freedom deletion"),
   "acl"		=>"FREEDOM"
  ),
  array(
   "name"		=>"FREEDOM_ADMIN",
   "acl"		=>"FREEDOM_MASTER",
   "layout"		=>"freedom_admin.xml",
   "short_name"		=>N_("Administration")
  ),
  array(
   "name"		=>"FREEDOM_UPDATETITLE",
   "short_name"		=>N_("Freedom update title fields"),
   "acl"		=>"FREEDOM_MASTER",
   "function"           =>"freedom_updatetitle",
   "layout"		=>"freedom_admin.xml",
   "script"		=>"freedom_admin.php"
	
  ),
  array(
   "name"		=>"FREEDOM_ACCESS",
   "short_name"		=>N_("Freedom accessibilities"),
   "acl"		=>"FREEDOM"	
  ),
  array(
   "name"		=>"FREEDOM_INIT",
   "short_name"		=>N_("Freedom initialisation"),
   "acl"		=>"FREEDOM"	
  )
                      );
   
?>
