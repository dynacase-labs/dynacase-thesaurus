<?php
// ---------------------------------------------------------------
// $Id: unlockfile.php,v 1.1 2003/01/24 14:10:45 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/unlockfile.php,v $
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
function unlockfile(&$action) 
{
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $auto = (GetHttpVars("auto","N")=="Y"); // just auto unlock



  $doc= new Doc($dbaccess,$docid);




  $err=$doc->UnLock($auto);
  if ($err != "") $action->ExitError($err);
  

  if (! $auto)  $action->AddLogMsg(sprintf(_("%s has been unlocked"),$doc->title));

    
  
    
  redirect($action,"FDL","FDL_CARD&id=".$doc->id,$action->GetParam("CORE_STANDURL"));
}



?>
