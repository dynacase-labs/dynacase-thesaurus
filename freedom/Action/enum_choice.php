<?php

// ---------------------------------------------------------------
// $Id: enum_choice.php,v 1.4 2001/12/19 17:57:32 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Attic/enum_choice.php,v $
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


include_once("FREEDOM/Class.Doc.php");
include_once("FREEDOM/Class.DocAttr.php");

function enum_choice(&$action) {
  
  // list of choice to be insert in attribute values

  $docid = GetHttpVars("docid",0);        // document being edition
  $attrid = GetHttpVars("attrid",0); // attribute need to enum
  $sorm = GetHttpVars("sorm","single"); // single or multiple
  $wname = GetHttpVars("wname",""); // single or multiple


  //global $HTTP_POST_VARS;print_r($HTTP_POST_VARS);
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc= new Doc($dbaccess,$docid);
  $oattr= $doc->GetAttribute($attrid);


  if (! include_once("PLUGGINGS/$oattr->phpfile")) {
    $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $oattr->phpfile));
  }



  if (! ereg("(.*)\((.*)\)\:(.*)", $oattr->phpfunc, $reg))
    $action->exitError(sprintf(_("the pluggins function description '%s' is not conform"), $oattr->phpfunc));

  $rargids = split(",",$reg[3]); // return args
  
  $sattrid="[";
  $sattrid.= implode(",", $rargids);
  $sattrid.="]";

  $argids = split(",",$reg[2]);  // input args
  while (list($k, $v) = each($argids)) {
    if ($v == "A") $arg[$k]= &$action;
    else if ($v == "D") $arg[$k]= $dbaccess;
    else if ($v == "T") $arg[$k]= &$this;
    else $arg[$k]= GetHttpVars($v,"");
  }

  
  
  $res = call_user_func_array($reg[1], $arg);
  

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
    $action->lay->Set("multiple", "");
    $action->lay->Set("nselect", 1);
  } else {
    $action->lay->Set("multiple", "multiple");
    $action->lay->Set("nselect", (count($tselect)>7)?7:count($tselect));
  }
  $action->lay->Set("attrid", $sattrid);
  $action->lay->SetBlockData("SELECT", $tselect);
  $action->lay->SetBlockData("ATTRVAL", $tval);
}


?>
