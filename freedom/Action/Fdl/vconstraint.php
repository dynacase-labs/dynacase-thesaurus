<?php
/**
 * Verify constraint on special attribute
 *
 * @author Anakeen 2003
 * @version $Id: vconstraint.php,v 1.2 2003/12/17 17:25:27 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */
 /**
 */

include_once("FDL/Class.DocFam.php");
include_once("FDL/modcard.php");

function vconstraint(&$action) {

  
  $docid = GetHttpVars("id",0);
  $famid=GetHttpVars("famid",GetHttpVars("classid"));
  $attrid=GetHttpVars("attrid");
  $index = GetHttpVars("index",-1); // index of the attributes for arrays
  $domindex = GetHttpVars("domindex",""); // index in dom of the attributes for arrays


  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  if ($docid > 0) {
    $doc = new Doc($dbaccess, $docid);


    

  } else {

    $doc = createDoc($dbaccess, $famid);
  }
  setPostVars($doc);
  

  


  $res=$doc->verifyConstraint($attrid,$index);

  if (is_array($res)) { // error with suggestion
    
    $action->lay->Set("error", $res["err"]);
    $action->lay->Set("iserror",($res["err"]=="")?"":"ko");
    $rargids=array($attrid);
    while (list($k, $v) = each($rargids)) {
      $rargids[$k].=$domindex;
    }
    $sattrid="[";
    $sattrid.= strtolower("'".implode("','", $rargids)."'");
    $sattrid.="]";
    $action->lay->Set("attrid", $sattrid);

    // list suggestion
    $tres=array();
    foreach ($res["sug"] as $sug) {
      $tres[]= array($sug, $sug);
    }

    // view possible correction
    while (list($k, $v) = each($tres)) {
      $tselect[$k]["choice"]= $v[0];
      $tselect[$k]["cindex"]= $k;
      $tval[$k]["index"]=$k;
      array_shift($v);
      
      $tval[$k]["attrv"]="['".implode("','", $v)."']";
    

    
    }
    $action->lay->SetBlockData("SELECT", $tselect);
    $action->lay->SetBlockData("ATTRVAL", $tval);
  }
  
  
}