<?php

// ---------------------------------------------------------------
// $Id: enum_choice.php,v 1.8 2002/09/10 13:30:27 eric Exp $
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

  $docid = GetHttpVars("docid",0);        // document being edition
  $attrid = GetHttpVars("attrid",0); // attribute need to enum
  $sorm = GetHttpVars("sorm","single"); // single or multiple
  $wname = GetHttpVars("wname",""); // single or multiple

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



  if (! ereg("(.*)\((.*)\)\:(.*)", $oattr->phpfunc, $reg))
    $action->exitError(sprintf(_("the pluggins function description '%s' is not conform"), $oattr->phpfunc));


  $rargids = split(",",$reg[3]); // return args
  
  $sattrid="[";
  $sattrid.= "'".implode("','", $rargids)."'";
  $sattrid.="]";

  $argids = split(",",$reg[2]);  // input args
  while (list($k, $v) = each($argids)) {
    if ($v == "A") $arg[$k]= &$action;
    else if ($v == "D") $arg[$k]= $dbaccess;
    else if ($v == "T") $arg[$k]= &$this;
    else $arg[$k]= trim(GetHttpVars("_".$v,""));
  }

  $res = call_user_func_array($reg[1], $arg);


  // addslahes for JS array
  reset($res);
   while (list($k, $v) = each($res)) {
     while (list($k2, $v2) = each($v)) {
       // not for the title
       if ($k2>0) $res[$k][$k2]=addslashes(str_replace("\r","",str_replace("\n","\\n",$v2))); // because JS array 
     }
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

  $action->lay->Set("wname", $wname);
  if ($sorm == "single") {

    $action->lay->SetBlockData("SELECTSINGLE", array(array("zou")));

  } else {

    $action->lay->SetBlockData("SELECTMULTIPLE", array(array("zou")));
    $action->lay->Set("nselect", (count($tselect)>7)?7:count($tselect));
  }
  $action->lay->Set("attrid", $sattrid);
  $action->lay->SetBlockData("SELECT", $tselect);
  $action->lay->SetBlockData("ATTRVAL", $tval);
}


?>
