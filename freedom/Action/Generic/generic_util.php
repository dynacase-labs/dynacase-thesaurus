<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_util.php,v 1.15 2004/06/28 16:05:26 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_util.php,v 1.15 2004/06/28 16:05:26 eric Exp $
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


function getDefFam(&$action) {
  
  // special for onefam application
  $famid=GetHttpVars("famid");
  if ($famid > 0) return $famid;

  $famid = $action->GetParam("DEFAULT_FAMILY", 1); 
  if ($famid==1) {
    $famid=$action->Read("DEFAULT_FAMILY", 0);
    $action->parent->SetVolatileParam("DEFAULT_FAMILY",$famid);
  }
  
  return $famid;
}

function getDefFld(&$action) {
  $famid=getDefFam($action);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fdoc = new DocFam($dbaccess,$famid);
  if ($fdoc->dfldid > 0) return $fdoc->dfldid;
  

  return 10;
}
// return attribute sort default
function getDefUSort(&$action) {
  $famid=getDefFam($action);
  $pu = $action->GetParam("GENERIC_USORT");
  if ($pu) {
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder,$sqlorder) = explode(":",$v);
      if ($afamid == $famid) return $aorder;
    }
  }
  return "title";
}
// return attribute split mode
function getSplitMode(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  $tmode= explode(",",$action->getParam("GENE_SPLITMODE"));

  // explode parameters
  while (list($k,$v) = each($tmode)) {
    list($fid,$vmode)=explode("|",$v);
    if ($fid == $famid) return $vmode;
  }
  return "V";
}

function getDefUSqlSort(&$action) {
  $famid=getDefFam($action);
  $pu = $action->GetParam("GENERIC_USORT");
  if ($pu) {
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder,$sqlorder) = explode(":",$v);
      if ($afamid == $famid) return $sqlorder;
    }
  }
  return "title";
}
// -----------------------------------
function getChildCatg($docid, $level,$notfldsearch=false,$maxlevel=2) {
  // -----------------------------------
  global $dbaccess;
  global $action;

  $ltree=array();


  if ($level <= $maxlevel) {
    $ldir = getChildDir($dbaccess,$action->user->id,$docid, $notfldsearch,"TABLE");
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree[$v["id"]] = array("level"=>$level*20,
				 "id"=>$v["id"],
				 "doctype"=>$v["doctype"],
				 "title"=>$v["title"]);

	if ($v["doctype"] == "D") $ltree = $ltree +  getChildCatg($v["id"], $level+1, $notfldsearch,$maxlevel );
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
  return GetSqlCond(array_merge(array($docid),array_keys($fdoc->GetChildFam())),"fromid");
  
}

?>