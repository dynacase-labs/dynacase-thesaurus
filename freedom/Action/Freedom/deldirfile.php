<?php
// ---------------------------------------------------------------
// $Id: deldirfile.php,v 1.3 2002/02/22 15:34:54 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/deldirfile.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2002
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


include_once("FDL/Class.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function deldirfile(&$action) {
  // -----------------------------------


  //print_r($HTTP_POST_VARS);

  // Get all the params      
  $dirid=GetHttpVars("dirid");
  $docid=GetHttpVars("docid");

  //  print "deldirfile :: dirid:$dirid , docid:$docid";


  $dbaccess = $action->GetParam("FREEDOM_DB");


  $dir = new Dir($dbaccess,$dirid);// use initial id for directories
  $err = $dir->DelFile($docid);
  if ($err != "") $action->exitError($err);

  
  
  redirect($action,GetHttpVars("app"),"FREEDOM_LIST&dirid=$dirid");
}




?>
