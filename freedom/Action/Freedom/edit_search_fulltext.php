<?php
// ---------------------------------------------------------------
// $Id: edit_search_fulltext.php,v 1.1 2002/08/05 16:11:12 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/edit_search_fulltext.php,v $
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


// -----------------------------------
function edit_search_fulltext(&$action) {
  // -----------------------------------
  
  $tmatch   = array( UDM_MODE_ALL => _("all"),
		     UDM_MODE_ANY => _("any"), 
		     UDM_MODE_BOOL => _("boolean"), 
		     UDM_MODE_PHRASE => _("full phrase") );
  $tsearch  = array( UDM_MATCH_WORD => _("word"), 
		     UDM_MATCH_SUBSTR => _("substring"), 
		     UDM_MATCH_BEGIN => _("starting"), 
		     UDM_MATCH_END => _("ending") ); 

  $dbaccess = $action->GetParam("FREEDOM_DB");
  

  // Get all the params      
  $dir=GetHttpVars("dirid"); // insert search in this folder
  $action->lay->Set("dirid", $dir);

  
  while (list($k,$v)= each ($tmatch)) {
    $selectmatch[$k]["idmatch"]=$k;
    $selectmatch[$k]["matchdescr"]=$action->Text($v);
  }
  
  while (list($k,$v)= each ($tsearch)) {
    $selectsearchfor[$k]["idsearchfor"]=$k;
    $selectsearchfor[$k]["searchfordescr"]=$action->Text($v);
  }
  

  $tclassdoc=GetClassesDoc($dbaccess, $action->user->id);

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc->initid;
    $selectclass[$k]["classname"]=$cdoc->title;
  }
  
  $action->lay->SetBlockData("SELECTMATCH",$selectmatch );
  $action->lay->SetBlockData("SELECTSEARCHFOR", $selectsearchfor);
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);
  
}


?>
