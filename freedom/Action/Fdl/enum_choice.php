<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: enum_choice.php,v 1.26 2004/02/05 15:43:22 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


// ---------------------------------------------------------------
// $Id: enum_choice.php,v 1.26 2004/02/05 15:43:22 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/enum_choice.php,v $
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
include_once("FDL/Class.DocAttr.php");

function enum_choice(&$action) {

  // list of choice to be insert in attribute values

  $docid = GetHttpVars("docid");        // document being edition
  if ($docid=="") $docid = GetHttpVars("fromid",0);        // in case of docid is null
  $attrid = GetHttpVars("attrid",0); // attribute need to enum
  $sorm = GetHttpVars("sorm","single"); // single or multiple
  $wname = GetHttpVars("wname",""); // single or multiple
  $index = GetHttpVars("index",""); // index of the attributes for arrays
  $domindex = GetHttpVars("domindex",""); // index in dom of the attributes for arrays

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  //global $HTTP_POST_VARS;print_r($HTTP_POST_VARS);
  $dbaccess = $action->GetParam("FREEDOM_DB");

 
  $doc= new Doc($dbaccess,$docid);
  $oattr= $doc->GetAttribute($attrid);
  if (! $oattr) 
    $action->exitError(sprintf(_("unknown attribute %s"), $attrid));


  if (! include_once("EXTERNALS/$oattr->phpfile")) {
    $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $oattr->phpfile));
  }


  $phpfunc=$oattr->phpfunc;
  // capture title
  $ititle="";
  
  if ($phpfunc[0] == "[") {
    if (ereg('\[(.*)\](.*)', $phpfunc, $reg)) {   
      $phpfunc=$reg[2];
      $ititle=addslashes($reg[1]);
    }
  }
  $action->lay->set("ititle",$ititle);
  if (! ereg("(.*)\((.*)\)\:(.*)", $phpfunc, $reg))
    $action->exitError(sprintf(_("the pluggins function description '%s' is not conform"), $phpfunc));


  $rargids = split(",",$reg[3]); // return args

  // change parameters familly
  $iarg =  preg_replace(
			"/\{([^\}]+)\}/e", 
			"getAttr('\\1')",
			$reg[2]);

  $argids = split(",",$iarg);  // input args
  while (list($k, $v) = each($argids)) {
    if ($v == "A") $arg[$k]= &$action;
    else if ($v == "D") $arg[$k]= $dbaccess;
    else if ($v == "I") $arg[$k]= $doc->id;
    else if ($v == "T") $arg[$k]= &$doc;
    else {
      // can be values or family parameter
      $a = $doc->GetAttribute($v);
      if ($index === "") {
	$arg[$k]= trim(GetHttpVars("_".strtolower($v),$v));
	
      } else {
	if ($a && ($a->usefor=="Q")) {
	  $arg[$k]=$doc->getParamValue($v);
	} else if ($a && $a->inArray()) {
	  if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
	    $ta = GetHttpVars("_".strtolower($v),$v);
	  
	    $arg[$k]=trim($ta[$index]);
	  } else {
	    $ta = GetHttpVars("_".strtolower($v),$v);
	    unset($ta["-1"]); // suppress hidden row because not set yet

	    $arg[$k]= $ta;
	  }
	} else $arg[$k]= trim(GetHttpVars("_".strtolower($v),$v));
      }
      if ($a && ($a->usefor=="Q")) {
	if (GetHttpVars("_".strtolower($v),false)===false) $arg[$k]=$doc->getParamValue($v);
      } 
    }
  }


  $res = call_user_func_array($reg[1], $arg);

  // addslahes for JS array
  reset($res);
   while (list($k, $v) = each($res)) {
     while (list($k2, $v2) = each($v)) {
       // not for the title
       if ($k2>0) $res[$k][$k2]=addslashes(str_replace("\r","",str_replace("\n","\\n",$v2))); // because JS array 
       else $res[$k][$k2]=substr($res[$k][$k2],0,$action->getParam("ENUM_TITLE_SIZE",40));
     }
   }


   if (count($res) == 0) {
     $action->exitError(sprintf(_("no match for %s"),$oattr->labelText));
   }

   reset($res);
  $tselect = array();
  $tval = array();
  while (list($k, $v) = each($res)) {
    $tselect[$k]["choice"]= $v[0];
    $tselect[$k]["cindex"]= $k;
    $tval[$k]["index"]=$k;
    array_shift($v);

    $tval[$k]["attrv"]="['".implode("','", $v)."']";
    

    
  }

  if ($sorm == "single") {

    $action->lay->SetBlockData("SELECTSINGLE", array(array("zou")));

  } else {

    $action->lay->SetBlockData("SELECTMULTIPLE", array(array("zou")));
    $action->lay->Set("nselect", (count($tselect)>7)?7:count($tselect));
  }
  
  // add  index for return args
  while (list($k, $v) = each($rargids)) {
    $rargids[$k].=$domindex;
  }
  $sattrid="[";
  $sattrid.= strtolower("'".implode("','", $rargids)."'");
  $sattrid.="]";
  $action->lay->Set("attrid", $sattrid);
  $action->lay->SetBlockData("SELECT", $tselect);
  $action->lay->SetBlockData("ATTRVAL", $tval);
}

function getAttr($aid) {
  

  
  
  $r=GetParam($aid);
  if ($r == "") $r=getFamIdFromName(GetParam("FREEDOM_DB"),$aid);
  
  return $r;
      
}

?>
