<?php
// ---------------------------------------------------------------
// $Id: adddirfile.php,v 1.2 2002/02/13 14:31:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/adddirfile.php,v $
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
// $Log: adddirfile.php,v $
// Revision 1.2  2002/02/13 14:31:58  eric
// ajout usercard application
//
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.4  2001/11/21 17:03:54  eric
// modif pour création nouvelle famille
//
// Revision 1.3  2001/11/21 13:12:55  eric
// ajout caractéristique creation profil
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:13  eric
// gestion documentaire
//
// ---------------------------------------------------------------

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function adddirfile(&$action) {
  // -----------------------------------


  //print_r($HTTP_POST_VARS);

  // Get all the params      
  $dirid=GetHttpVars("dirid");
  $docid=GetHttpVars("docid");
  $mode=GetHttpVars("mode");


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $dir= new Dir($dbaccess, $dirid);

  $err = $dir->AddFile($docid, $mode);
  

  if ($err != "") $action->exitError($err);
  
  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_CARD&id=$docid");
}




?>
