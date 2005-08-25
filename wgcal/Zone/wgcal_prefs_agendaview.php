<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_agendaview.php,v 1.3 2005/08/25 16:02:16 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once('WGCAL/Lib.wTools.php');
include_once('WGCAL/Lib.WGCal.php');
include_once('FDL/Class.Doc.php');


function wgcal_prefs_agendaview(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $uid = GetHttpVars("uid", $action->user->id);
  $action->lay->set("uid", $uid);

  // default visibility for new event

  $ic= 0; 
  $avis = CAL_getEventVisibilities($dbaccess, "");
  foreach ($avis as $k => $v) {
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($action->getParam("WGCAL_U_RVDEFCONF",0)==$k?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("evdefconf", $tconf);
  if ($action->getParam("WGCAL_U_RVDEFCONF",0)==2) {
    $action->lay->set("fshowgroupsd", "");
  } else {
    $action->lay->set("fshowgroupsd", "none");
  }
    
  // Groups list
  
  $u_rgroups = wGetUserGroups();

  $gjs = array(); $igjs=0;
  $igroups = array(); $ig=0;
  foreach ($u_rgroups as $k => $v) {
    $gr = new Doc($dbaccess, $k);
    $igroups[$ig]["gfid"] = $gr->id;
    $igroups[$ig]["gid"] = $gr->getValue("us_whatid");
    $igroups[$ig]["gtitle"] = ucwords(strtolower($gr->title));
    $igroups[$ig]["gicon"] = $gr->GetIcon();
    $igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($gr->title)));
    $igroups[$ig]["gisused"] = $v["sel"];
    if ($v["sel"]) {
      $gjs[$igjs]["igroup"] = $igjs;
      $gjs[$igjs]["gfid"] = $gr->id;
      $igjs++;
    }    
    $ig++;
  }  
  $action->lay->setBlockData("groups", $igroups);
  $action->lay->setBlockData("GLIST", $gjs);
  

  // Agenda visibility

  if (!$action->HasPermission("WGCAL_VCAL")) {

    $action->lay->set("showvcal", false);

  } else {

    $action->lay->set("showgroups", false);
    $action->lay->set("showvcal", true);
    $user = new Doc($dbaccess, $action->user->fid);

    $vcalmode = $user->getValue("us_wgcal_vcalgrpmode");
    $vcalgrp  = $user->getTValue("us_wgcal_vcalgrpid");
    $vcalgrpw  = $user->getTValue("us_wgcal_vcalgrpwrite");
    $action->lay->set("allsel", ($vcalmode==1 ? false : true));
    $action->lay->set("vgroup", ($vcalmode==1 ? "" : "none"));
    $action->lay->set("vcalmode", ($vcalmode==1 ? 1 : 0));
    $action->lay->set("vcalgroups", implode("|",$vcalgrp));

    // compute user real groups (user groups used in Agenda)
    $wgcal_groups = wGetGroups();
    $user_groups = $user->getTValue("us_idgroup");
    $u_rgroups = array();
    foreach ($user_groups as $kg => $vg) {
      if (isset($wgcal_groups[$vg])) {
	$u_rgroups[$vg]["gid"] = $vg;
	$u_rgroups[$vg]["sel"] = false;
	$u_rgroups[$vg]["wri"] = false;
      }
    }

    foreach ($vcalgrp as $kg => $vg) {
      if (isset($u_rgroups[$vg])) {
	$u_rgroups[$vg]["sel"] = true;
	if ($vcalgrpw[$kg]==1) $u_rgroups[$vg]["wri"] = true;
      }
    }

    $gjs = array(); $igjs=0;
    $igroups = array(); $ig=0;
    if (count($u_rgroups)>0) {

      $action->lay->set("showgroups", true);
      foreach ($u_rgroups as $k => $v) {
	$gr = new Doc($dbaccess, $k);
	$igroups[$ig]["gfid"] = $gr->id;
	$igroups[$ig]["gid"] = $gr->getValue("us_whatid");
	$igroups[$ig]["gtitle"] = ucwords(strtolower($gr->title));
	$igroups[$ig]["gicon"] = $gr->GetIcon();
	$igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($gr->title)));
	$igroups[$ig]["gisused"] = $v["sel"];
	$igroups[$ig]["gwriteselected"] = ($v["wri"] ? "checked" : "");
	$igroups[$ig]["gwvisibility"] = ($v["sel"] ? "visible" : "hidden");
	if ($v["sel"]) {
	  $gjs[$igjs]["igroup"] = $igjs;
	  $gjs[$igjs]["gfid"] = $gr->id;
	  $gjs[$igjs]["gmode"] = ($v["wri"] ? "true" : "false");
	  
	  $igjs++;
	}    
	$ig++;
      }  
    }
    $action->lay->setBlockData("cgroups", $igroups);
    $action->lay->setBlockData("GCLIST", $gjs);
  }
  return;

}