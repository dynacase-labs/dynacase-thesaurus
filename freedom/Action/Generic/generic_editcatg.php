<?php
// ---------------------------------------------------------------
// $Id: generic_editcatg.php,v 1.1 2003/03/28 17:53:56 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_editcatg.php,v $
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
include_once("GENERIC/generic_util.php"); 

// -----------------------------------
function generic_editcatg(&$action) {
  // -----------------------------------

  global $dbaccess;
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $aid = GetHttpVars("aid"); // attribute id
  $famid = GetHttpVars("fid"); // family id

  $action->lay->set("aid",$aid);
  $action->lay->set("fid",$famid);
  $doc = new Doc($dbaccess, $famid);

  $a = $doc->getAttribute($aid);

  $action->lay->set("TITLE",sprintf(_("definition of enumerate attribute %s of %s family"),
				   $a->labelText, $doc->title));
  $tref=array();
  $tlabel=array();
  $tlevel=array();
  while (list($k, $v) = each($a->enum)) {
    $tk= explode(".",$k);
    $tv= explode("/",$v);
    $sp ="";
    $loff ="";
    for ($i=1;$i<count($tk);$i++) $loff .= ".....";
    
    $tlevel[]= array("alevel"=>count($tk));
    $tref[]= array("eref"=>array_pop($tk));
    $vlabel = array_pop($tv);
    $tlabel[]= array("elabel"=>$vlabel,
		     "velabel"=>$loff.$vlabel);
  }

  $action->lay->setBlockData("ALEVEL",$tlevel);
  $action->lay->setBlockData("AREF",$tref);
  $action->lay->setBlockData("ALABEL",$tlabel);
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/GENERIC/Layout/generic_editcatg.js");


}


?>
