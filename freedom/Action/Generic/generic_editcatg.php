<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_editcatg.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



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
  $doc = new_Doc($dbaccess, $famid);

  $err = $doc->control("edit"); // need edit permission
  if ($err != "") $action->exitError($err);

  $a = $doc->getAttribute($aid);
  $action->lay->set("fid",$a->docid);


  $action->lay->set("TITLE",sprintf(_("definition of enumerate attribute %s of %s family"),
				   $a->labelText, $doc->title));
  $tref=array();
  $tlabel=array();
  $tlevel=array();

  $enum = $a->getEnum();
  while (list($k, $v) = each($enum)) {
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
