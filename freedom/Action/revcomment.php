<?php
// ---------------------------------------------------------------
// $Id: revcomment.php,v 1.1 2001/11/21 08:40:34 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/revcomment.php,v $
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
// $Log: revcomment.php,v $
// Revision 1.1  2001/11/21 08:40:34  eric
// ajout historique
//
// Revision 1.3  2001/11/16 18:04:39  eric
// modif de fin de semaine
//
// Revision 1.2  2001/11/15 17:51:50  eric
// structuration des profils
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//

// ---------------------------------------------------------------

include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");
function revcomment(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);


  $doc= newDoc($dbaccess,$docid);


  
  $err = $doc->CanUpdateDoc();
  if ($err != "") $action->ExitError($err);
  

  $action->lay->Set("title", $doc->title);
  $action->lay->Set("docid", $doc->id);


    


}

?>
