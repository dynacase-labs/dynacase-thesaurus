<?php
// ---------------------------------------------------------------
// $Id: freedom_access.php,v 1.4 2002/11/13 15:49:36 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_access.php,v $
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




  // -----------------------------------
function freedom_access(&$action) {
  // -----------------------------------
  // export all selected card in a tempory file
  // this file is sent by dowload  
  // -----------------------------------

  // Get all the params   
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid= GetHttpVars("id");
  $userId= GetHttpVars("userid",$action->user->id);



  $doc = new Doc($dbaccess, $docid);

  $action->lay->Set("title", $doc->title);
    // contruct user id list


    $ouser = new User();
    $tiduser = $ouser->GetUserAndGroupList();
    $userids= array();
    while(list($k,$v) = each($tiduser)) {
      if ($v->id == 1) continue; // except admin : don't need privilege
      if ($v->id == $userId) $userids[$k]["selecteduser"] = "selected";
      else $userids[$k]["selecteduser"]="";
      $userids[$k]["suserid"]= $v->id;
      $userids[$k]["descuser"]=$v->firstname." ".$v->lastname;
    }

    $action->lay->Set("docid", $doc->id);
    $action->lay->Set("userid", ($userId==1)?$tiduser[0]->id:$userId);

    $action->lay->SetBlockData("USER",$userids); 
  
}



?>
