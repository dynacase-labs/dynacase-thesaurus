<?php
// ---------------------------------------------------------------
// $Id: foliosel.php,v 1.1 2003/02/05 17:04:21 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/foliosel.php,v $
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
function foliosel(&$action) {
  // -----------------------------------

  // Get all the params      
  $selid=GetHttpVars("selid",0); // 
  $selected=(GetHttpVars("selected","N")=="Y"); // is selected


  //  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->lay->set("selid",$selid);
  $action->lay->set("selected",$selected?"true":"false");
}

?>