<?php
// ---------------------------------------------------------------
// $Id: generic_editchangecatg.php,v 1.5 2002/11/15 16:17:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_editchangecatg.php,v $
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


include_once("FDL/Class.Dir.php");
include_once("FDL/Class.DocUser.php");
include_once("GENERIC/generic_util.php");

// -----------------------------------
function generic_editchangecatg(&$action) {
  // -----------------------------------
  global $docid;
  global $dbaccess;

  $docid=GetHttpVars("id"); // the user to change catg

  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new Dir( $dbaccess,getDefFld($action) );

  $doc = new Doc($dbaccess,$docid);
  $action->lay->Set("username",$doc->title);



  $stree=getChildCatg($homefld->id, 1);

  reset($stree);
  
  while (list($k,$v) = each($stree)) {
    if (isInDir($dbaccess, $v["id"], $docid)) $checked="checked";
    else  $checked="";
    $stree[$k]["checked"]=$checked;
  }
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topdir",getDefFld($action));
  $action->lay->Set("docid",$docid);
  

}

?>
