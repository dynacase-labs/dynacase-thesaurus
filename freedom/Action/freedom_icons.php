<?php
// ---------------------------------------------------------------
// $Id: freedom_icons.php,v 1.14 2001/11/28 13:40:10 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_icons.php,v $
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
// $Log: freedom_icons.php,v $
// Revision 1.14  2001/11/28 13:40:10  eric
// home directory
//
// Revision 1.13  2001/11/27 13:09:08  eric
// barmenu & modif popup
//
// Revision 1.12  2001/11/26 18:01:01  eric
// new popup & no lock for no revisable document
//
// Revision 1.11  2001/11/22 17:49:13  eric
// search doc
//
// Revision 1.10  2001/11/22 10:00:59  eric
// premier pas vers une API pour les popup
//
// Revision 1.9  2001/11/21 17:03:54  eric
// modif pour création nouvelle famille
//
// Revision 1.8  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.7  2001/11/21 08:38:58  eric
// ajout historique + modif sur control object
//
// Revision 1.6  2001/11/19 18:04:22  eric
// aspect change
//
// Revision 1.5  2001/11/16 18:04:39  eric
// modif de fin de semaine
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
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------

include_once('FREEDOM/freedom_view.php');



// -----------------------------------
// -----------------------------------
function freedom_icons(&$action) {
// -----------------------------------
  // Set the globals elements

  $action->Register("freedom_view","icon");
  view_folder($action, false);
  


}
?>
