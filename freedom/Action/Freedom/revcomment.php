<?php
// ---------------------------------------------------------------
// $Id: revcomment.php,v 1.3 2002/09/19 13:45:10 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/revcomment.php,v $
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
include_once("FDL/Class.DocAttr.php");
function revcomment(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);


  $doc= new Doc($dbaccess,$docid);


  $err= $doc -> lock(true); // auto lock
  if ($err != "")    $action-> ExitError($err);
  
  $err = $doc->CanUpdateDoc();
  if ($err != "") $action->ExitError($err);
  

  $action->lay->Set("title", $doc->title);
  $action->lay->Set("docid", $doc->id);


    


}

?>
