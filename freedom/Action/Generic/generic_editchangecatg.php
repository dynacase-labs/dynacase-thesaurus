<?php
// ---------------------------------------------------------------
// $Id: generic_editchangecatg.php,v 1.2 2002/06/19 12:32:29 eric Exp $
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

// -----------------------------------
function generic_editchangecatg(&$action) {
  // -----------------------------------
  global $docid;
  global $dbaccess;

  $docid=GetHttpVars("id"); // the user to change catg

  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new Dir( $dbaccess, $action->GetParam("DEFAULT_FLD"));

  $doc = new Doc($dbaccess,$docid);
  $action->lay->Set("username",$doc->title);



  $stree=getChildCatg($homefld, 1);

  reset($stree);
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topdir",$action->GetParam("DEFAULT_FLD"));
  $action->lay->Set("docid",$docid);
  

}


// -----------------------------------
function getChildCatg($doc, $level) {
  // -----------------------------------
  global $dbaccess;
  global $action;
  global $docid;
  

  $ltree=array();

    $ldir = getChildDir($dbaccess,$action->user->id, $doc->id, true);
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	if (isInDir($dbaccess, $v->id, $docid)) $checked="checked";
	else  $checked="";
	$ltree[] = array("level"=>$level*20,
			 "id"=>$v->id,
			 "title"=>$v->title,
			 "checked"=>$checked);
	$ltree = array_merge($ltree, getChildCatg($v, $level+1));
      }
    } 
  
  return $ltree;
}
?>
