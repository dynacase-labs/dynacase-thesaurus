<?php
// ---------------------------------------------------------------
// $Id: adddirfile.php,v 1.3 2001/11/21 13:12:55 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/adddirfile.php,v $
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

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.QueryDir.php");
include_once("FREEDOM/freedom_util.php");  



// -----------------------------------
function adddirfile(&$action) {
  // -----------------------------------


  //print_r($HTTP_POST_VARS);

  // Get all the params      
  $dirid=GetHttpVars("dirid");
  $docid=GetHttpVars("docid");
  $mode=GetHttpVars("mode");


  $dbaccess = $action->GetParam("FREEDOM_DB");


  switch ($mode) {
  case "static":
    $query="select id from doc where id=".$docid;
  break;
  case "latest":
    $doc= new Doc($dbaccess, $docid);
    $query="select id from doc where initid=".$doc->initid." order by revision DESC LIMIT 1";
  break;
  default:
    $query="select id from doc where id=".$docid;
  break;
  }  

  $qf = new QueryDir($dbaccess);

  $dir= new Doc($dbaccess, $dirid);
  $qf->dirid=$dir->initid; // the reference directory is the initial id
  $qf->query=$query;
  $qf->qtype='S'; // single user query
  $qf->Add();

  

  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_CARD&id=$docid");
}




?>
