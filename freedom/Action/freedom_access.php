<?php
// ---------------------------------------------------------------
// $Id: freedom_access.php,v 1.1 2001/11/09 09:41:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/freedom_access.php,v $
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
// $Log: freedom_access.php,v $
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
// Revision 1.1  2001/10/10 16:01:31  eric
// modif pour les droits d'accès
//

//
//
// ---------------------------------------------------------------
include_once("FREEDOM/Class.Doc.php");




  // -----------------------------------
function freedom_access(&$action) {
  // -----------------------------------
  // export all selected card in a tempory file
  // this file is sent by dowload  
  // -----------------------------------

  // Get all the params   
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid= GetHttpVars("id");



  $ofreedom = new Doc($dbaccess, $docid);



  
  redirect($action,"ACCESS","EDIT_OBJECT&sole=Y&mod=app&isclass=yes&userid={$action->parent->user->id}&appid={$ofreedom->classid}&oid={$ofreedom->oid}");
}



?>
