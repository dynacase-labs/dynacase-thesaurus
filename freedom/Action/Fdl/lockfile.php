<?php
// ---------------------------------------------------------------
// $Id: lockfile.php,v 1.2 2003/04/23 10:00:48 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/lockfile.php,v $
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


include_once("FDL/Class.Doc.php");
function lockfile(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);

  $action->lay->Set("docid",$docid);

  $doc= new Doc($dbaccess,$docid);

  


  
  $err=$doc->lock();
  if ($err != "") $action->ExitError($err);
  
  
  $action->AddLogMsg(sprintf(_("%s has been locked"),$doc->title));
    
  
    
  redirect($action,"FDL","FDL_CARD&props=Y&id=".$doc->id,$action->GetParam("CORE_STANDURL"));

}



?>
