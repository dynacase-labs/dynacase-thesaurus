<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: enum_choice.php,v 1.36 2004/10/19 16:05:23 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");

function enum_choice(&$action) {

  // list of choice to be insert in attribute values

  $docid = GetHttpVars("docid");        // document being edition
  if ($docid=="") $docid = GetHttpVars("fromid",0);        // in case of docid is null
  $attrid = GetHttpVars("attrid",0); // attribute need to enum
  $sorm = GetHttpVars("sorm","single"); // single or multiple
  $index = GetHttpVars("index",""); // index of the attributes for arrays
  $domindex = GetHttpVars("domindex",""); // index in dom of the attributes for arrays

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc= new Doc($dbaccess,$docid);
  $oattr= $doc->GetAttribute($attrid);
  if (! $oattr) 
    $action->exitError(sprintf(_("unknown attribute %s"), $attrid));

  $notalone="true";

  if (ereg("([a-z]*)-alone",$sorm,$reg)) {
    $sorm=$reg[1];
    $notalone="false";
  }
  $action->lay->set("notalone",$notalone);

  $action->parent->AddJsRef($action->GetParam("CORE_STANDURL")."app=FDL&action=ENUMCHOICEJS");
  $phpfunc=$oattr->phpfunc;
  // capture title
  $ititle="";
  
  if ($phpfunc[0] == "[") {
    if (ereg('\[(.*)\](.*)', $phpfunc, $reg)) {   
      $oattr->phpfunc=$reg[2];
      
      $ititle=addslashes($reg[1]);
    }
  }
  $action->lay->set("ititle",$ititle);

  $res=getResPhpFunc($doc,$oattr,$rargids,$tselect,$tval,true,$index);

 
  if (count($res) == 0) {
    $action->exitError(sprintf(_("no match for %s"),$oattr->labelText));
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

function enumjschoice(&$action) {
  $sorm = GetHttpVars("sorm","single"); // single or multiple
  $notalone="true";

  if (ereg("([a-z]*)-alone",$sorm,$reg)) {
    $sorm=$reg[1];
    $notalone="false";
  }
  $action->lay->set("notalone",$notalone);
}

function getFuncVar($n,$def="",$whttpvars,&$doc,&$oa) {
   
    if ($whttpvars) return GetHttpVars("_".strtolower($n),$def);
    else {
      $h=GetHttpVars(strtolower($n));
      if ($h) return $h;
      if ($oa->repeat) $r= $doc->getTValue($n);
      else $r=$doc->getValue($n);
      if ($r==="") return false;
      return $r;
    }
    
}
function getResPhpFunc(&$doc,&$oattr,&$rargids,&$tselect,&$tval,$whttpvars=true,$index="") { 
  global $action;

  if (! include_once("EXTERNALS/$oattr->phpfile")) {
    $action->exitError(sprintf(_("the external pluggin file %s cannot be read"), $oattr->phpfile));
  }
  $phpfunc=$oattr->phpfunc;
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
    if ($v == "A") {global $action;$arg[$k]= &$action;}
    else if ($v == "D") $arg[$k]= $doc->dbaccess;
    else if ($v == "I") $arg[$k]= $doc->id;
    else if ($v == "T") $arg[$k]= &$doc;
    else {
      // can be values or family parameter
      $a = $doc->GetAttribute($v);
      if ($index === "") {

	$ta=getFuncVar($v,$v,$whttpvars,$doc,$a);
	if ($ta === false) return false;
	
	if (is_array($ta)) {
	  unset($ta["-1"]); // suppress hidden row because not set yet
	  $arg[$k]=$ta;
	} else $arg[$k]= trim($ta);
	
      } else {
	if ($a && ($a->usefor=="Q")) {
	   if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
	    $ta = getFuncVar($v,$v,$whttpvars,$doc,$a);
	    if ($ta === false) return false;
	    $arg[$k]=trim($ta[$index]);
	   } else {
	     $arg[$k]=$doc->getParamValue($v);
	   }
	} else if ($a && $a->inArray()) {
	  if (($a->fieldSet->id == $oattr->fieldSet->id)) { // search with index
	    $ta = getFuncVar($v,$v,$whttpvars,$doc,$a);
	    if ($ta === false) return false;
	    $arg[$k]=trim($ta[$index]);
	  } else {
	    $ta = getFuncVar($v,$v,$whttpvars,$doc,$a);	   
	    if ($ta === false) return false;
	    unset($ta["-1"]); // suppress hidden row because not set yet

	    $arg[$k]= $ta;
	  }
	} else {
	  $ta= getFuncVar($v,$v,$whttpvars,$doc,$a); 
	  if ($ta === false) return false;
	  $arg[$k]= trim($ta);
	}
      }
      if ($a && ($a->usefor=="Q")) {
	if (getFuncVar($v,false,$whttpvars,$doc,$a)===false) $arg[$k]=$doc->getParamValue($v);
      } 
    }
  }

  $res = call_user_func_array($reg[1], $arg);



  if (count($res) > 0) {

  // addslahes for JS array
  reset($res);
  while (list($k, $v) = each($res)) {
     while (list($k2, $v2) = each($v)) {
       // not for the title
       if ($k2>0) $res[$k][$k2]=addslashes(str_replace("\r","",str_replace("\n","\\n",$v2))); // because JS array 
       else $res[$k][$k2]=substr($res[$k][$k2],0,$action->getParam("ENUM_TITLE_SIZE",40));
     }
   }
    $tselect = array();
    $tval = array();
    reset($res);
    $ki=0;
    while (list($k, $v) = each($res)) {
      $tselect[$k]["choice"]= $v[0];
      $tselect[$k]["cindex"]= $ki; // numeric index needed
      $tval[$k]["index"]=$ki;
      array_shift($v);

      $tval[$k]["attrv"]="['".implode("','", $v)."']";
      $ki++;

    }
  }

  return $res;
  
}

function getAttr($aid) {      
  $r=GetParam($aid);
  if ($r == "") $r=getFamIdFromName(GetParam("FREEDOM_DB"),$aid);
  
  return $r;
      
}
?>
