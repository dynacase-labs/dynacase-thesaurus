<?php
// ---------------------------------------------------------------
// $Id: generic_del.php,v 1.1 2002/04/17 09:03:12 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_del.php,v $
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
// $Log: generic_del.php,v $
// Revision 1.1  2002/04/17 09:03:12  eric
// app GENERIC => PRODUCT CONTRACT SITE
//
// Revision 1.1  2002/02/19 11:11:02  eric
// modif sur les popup des documents
//
// Revision 1.2  2002/02/13 14:31:58  eric
// ajout usercard application
//
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.4  2002/01/25 09:37:06  eric
// suppression appel LDAP
//
// Revision 1.3  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.5  2001/09/10 16:51:45  eric
// ajout accessibilté objet
//
// Revision 1.4  2001/08/31 13:30:51  eric
// modif pour accessibilité
//
// Revision 1.3  2001/06/22 09:46:12  eric
// support attribut multimédia
//
// Revision 1.2  2001/06/19 16:08:17  eric
// correction pour type image
//
// Revision 1.1  2001/06/13 14:39:53  eric
// Freedom address book
//
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Class.DocValue.php");
include_once("FDL/freedom_util.php");

// -----------------------------------
function generic_del(&$action) {
// -----------------------------------


  // Get all the params      
  $docid=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
   
  if ( $docid == "" )
    return;


  $doc = new DocUser($dbaccess, $docid);
  
  // ------------------------------
  // delete POSGRES card

  $doc-> Delete();
      
    

  
  redirect($action,GetHttpVars("app"),"GENERIC_LOGO");

}
?>
