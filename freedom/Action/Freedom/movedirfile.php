<?php
// ---------------------------------------------------------------
// $Id: movedirfile.php,v 1.5 2003/03/27 09:42:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/movedirfile.php,v $
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


include_once("FDL/Lib.Dir.php");
include_once("FDL/freedom_util.php");  



// -----------------------------------
function movedirfile(&$action) {
  // -----------------------------------


  //print_r($HTTP_POST_VARS);

  // Get all the params      
  $todirid=GetHttpVars("todirid");
  $fromdirid=GetHttpVars("fromdirid");
  $docid=GetHttpVars("docid");
  $return=GetHttpVars("return"); // return action may be folio
;


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new Doc($dbaccess, $docid);

  // add before suppress
  $dir= new Doc($dbaccess, $todirid);
  $err = $dir->AddFile($docid);
  if ($err != "") $action->exitError($err);

  $action->AddLogMsg(sprintf(_("%s has been added in %s folder"),
			     $doc->title,
			     $dir->title));

  $dir= new Doc($dbaccess, $fromdirid);
  $err = $dir->DelFile($docid);
  if ($err != "") $action->exitError($err);
  $action->AddLogMsg(sprintf(_("%s has been removed in %s folder"),
			     $doc->title,
			     $dir->title));
  

  
  
  if ($return == "folio")  redirect($action,GetHttpVars("app"),"FOLIOLIST&dirid=$todirid");
  else redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$todirid");
  
}




?>
