<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_util.php,v 1.27 2007/05/04 16:11:40 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_util.php,v 1.27 2007/05/04 16:11:40 eric Exp $
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
  if (! is_numeric($famid)) $famid=getIdFromName( $action->GetParam("FREEDOM_DB"),$famid);
  if ($famid != "") return $famid;

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
  $fdoc = new_Doc($dbaccess,$famid);
  if ($fdoc->dfldid > 0) return $fdoc->dfldid;
  

  return 0;
}
// return attribute sort default
function getDefUSort(&$action,$def="title") {
  $famid=getDefFam($action);
  $pu = $action->GetParam("GENERIC_USORT");
  if ($pu) {
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder,$sqlorder) = explode(":",$v);
      if ($afamid == $famid) return $aorder;
    }
  }
  return $def;
}


// return parameters key search
function getDefUKey(&$action) {
  $famid=getDefFam($action);
  $pu = $action->GetParam("GENE_LATESTTXTSEARCH");
  if ($pu) {
    $tu = explode("|",$pu);
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder) = explode(":",$v);
      if ($afamid == $famid) return $aorder;
    }
  }
  return "";
}

/**
 * return parameters key search
 * @param action $action current action
 * @param string $key parameter name
 * return string the value of the parameter according to default family
*/
function getDefU(&$action,$key) {
  $famid=getDefFam($action);
  return getFamilyParameter($action,$key,$famid);
}

/**
 * return attribute split mode 
 * @return string [V|H] vertical or horizontal split according to family
 */
function getSplitMode(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_SPLITMODE","V");
}

/**
 * return attribute view mode 
 * @return string [abstract|column]  according to family
 */
function getViewMode(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_VIEWMODE","abstract");
}
/**
 * return attribute view tab letters
 * @return string [Y|N] Yes/No  according to family
 */
function getTabLetter(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_TABLETTER","Y");
}
/**
 * return  if search is also in inherit famileis 
 * @return string [Y|N] Yes/No  according to family
 */
function getInherit(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_INHERIT","Y");
}

/**
 * set attribute split mode
 * @param string $split [V|H]
 */
function setSplitMode(&$action,$famid,$split) {
  return setFamilyParameter($action,$famid,'GENE_SPLITMODE',$split);
}
/**
 * set attribute view mode
 * @param string $view [abstract|column]
 */
function setViewMode(&$action,$famid,$view) {
  return setFamilyParameter($action,$famid,'GENE_VIEWMODE',$view);
}
/**
 * set attribute view tab letters
 * @param string $letter [Y|N] Yes/No
 */
function setTabLetter(&$action,$famid,$letter) {
  return setFamilyParameter($action,$famid,'GENE_TABLETTER',$letter);
}
/**
 * set attribute view tab letters
 * @param string $inherit [Y|N] Yes/No
 */
function setInherit(&$action,$famid,$inherit) {
  return setFamilyParameter($action,$famid,'GENE_INHERIT',$inherit);
}

/**
 * return parameters key search
 * @param action $action current action
 * @param int $famid family identificator
 * @param string $key parameter name
 * return string the value of the parameter according to family
*/
function getFamilyParameter(&$action,$famid,$key,$def="") { 
  $pu = $action->GetParam($key);
  if ($pu) {
    $tu = explode(",",$pu);
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder) = explode("|",$v);
      if ($afamid == $famid) return $aorder;
    }
  }
  return $def;
}
/**
 * set family attribute for generic application
 */
function setFamilyParameter(&$action,$famid,$attrid,$value) {
  $tmode= explode(",",$action->getParam($attrid));

  // explode parameters
  while (list($k,$v) = each($tmode)) {
    list($fid,$vmode)=explode("|",$v);
    $tview[$fid]=$vmode;
  }
  
  $tview[$famid]=$value;
  // implode parameters to change user preferences
  $tmode=array();
  while (list($k,$v) = each($tview)) {
    if ($k>0) $tmode[]="$k|$v";
  }
  $pmode=implode(",",$tmode);
  $action->parent->param->Set($attrid,$pmode,PARAM_USER.$action->user->id,$action->parent->id);
  $action->parent->session->close();
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
				 "fromid"=>$v["fromid"],
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
  $fdoc= new_Doc( $dbaccess, $docid);
  $child= $fdoc->GetChildFam();
  return GetSqlCond(array_merge(array($docid),array_keys($fdoc->GetChildFam())),"fromid");
  
}

?>