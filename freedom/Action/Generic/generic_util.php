<?php
// ---------------------------------------------------------------
// $Id: generic_util.php,v 1.4 2002/11/04 17:56:17 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_util.php,v $
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

function getDefFld(&$action) {
  
  // special for onefam application
  $topdirid=$action->GetParam("DEFAULT_FLD",10);
  if ($topdirid==10) {
    $topdirid=$action->Read("DEFAULT_FLD", 10);
    $action->parent->SetVolatileParam("DEFAULT_FLD",$topdirid);
  }

  return $topdirid;
}

function getDefFam(&$action) {
  
  // special for onefam application
  $famid = $action->GetParam("DEFAULT_FAMILY", 1); 
  if ($famid==1) {
    $famid=$action->Read("DEFAULT_FAMILY", 1);
    $action->parent->SetVolatileParam("DEFAULT_FAMILY",$famid);
  }
  
  return $famid;
}

// -----------------------------------
function getChildCatg($doc, $level) {
  // -----------------------------------
  global $dbaccess;
  global $action;

  $ltree=array();


  if ($level < 4) {
    $ldir = getChildDir($dbaccess,$action->user->id,$doc->id, false,"TABLE");
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree[$v["id"]] = array("level"=>$level*20,
				 "id"=>$v["id"],
				 "doctype"=>$v["doctype"],
				 "title"=>$v["title"]);

	$ltree = $ltree +  getChildCatg($v, $level+1);
      }
    } 
  }
  return $ltree;
}

// -----------------------------------
function getSqlFrom($dbaccess, $docid) {
  // -----------------------------------
  $fdoc= new Doc( $dbaccess, $docid);
  $child= $fdoc->GetChildFam();
  return GetSqlCond(array_merge(array($docid),$fdoc->GetChildFam()),"fromid");
  
}

?>