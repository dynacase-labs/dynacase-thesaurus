<?php
// ---------------------------------------------------------------
// $Id: viewscard.php,v 1.2 2003/03/11 17:09:45 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/viewscard.php,v $
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
// -----------------------------------
function viewscard(&$action) {
  // -----------------------------------

  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $zonebodycard = GetHttpVars("zone"); // define view action
  $ulink = (GetHttpVars("ulink",'Y') == "Y"); // add url link
  $target = GetHttpVars("target"); // may be mail

  // Set the globals elements

  $dbaccess = $action->GetParam("FREEDOM_DB");


  $doc = new Doc($dbaccess, $docid);
  $err = $doc->control("view");
  if ($err != "") $action->exitError($err);
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $action->exitError(_("no zone specified"));


  $err=$doc->refresh();
  $action->lay->Set("ZONESCARD", $doc->viewDoc($zonebodycard,$target,$ulink,$abstract));
  
 

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");





}


?>
