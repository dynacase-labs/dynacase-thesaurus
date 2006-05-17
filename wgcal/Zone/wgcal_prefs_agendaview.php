<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_agendaview.php,v 1.10 2006/05/17 15:52:15 marc Exp $
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
include_once('WGCAL/Lib.Agenda.php');
include_once('FDL/Class.Doc.php');


function wgcal_prefs_agendaview(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $uid = GetHttpVars("uid", $action->user->id);
  $action->lay->set("uid", $uid);

  $duser = getDocFromUserId($dbaccess,$uid);
     
  // Groups list
  
  $u_rgroups = wGetUserGroups();

  $igroups = array(); $ig=0;
  $onsel = false;
  foreach ($u_rgroups as $k => $v) {
    $gr = new_Doc($dbaccess, $k);
    if (!$gr->isAffected()) continue;
    $igroups[$ig]["gfid"] = $gr->id;
    $igroups[$ig]["gid"] = $gr->getValue("us_whatid");
    $igroups[$ig]["gtitle"] = ucwords(strtolower($gr->title));
    $igroups[$ig]["gicon"] = $gr->GetIcon();
    $igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($gr->title)));
    $igroups[$ig]["gisused"] = $v["sel"];
    if ($v["sel"]) $onsel = true;
    $ig++;
  }  

  $gjs = array(); $igjs=0;
  foreach ($igroups as $k => $v) {
    if ($v["gisused"] || !$onsel) {
      $gjs[$igjs]["igroup"] = $igjs;
      $gjs[$igjs]["gfid"] = $v["gfid"];
      $igroups[$k]["gisused"] = true;
     $igjs++;
    }
  }

  $action->lay->set("GInit", !$onsel);
  $action->lay->setBlockData("groups", $igroups);
  $action->lay->setBlockData("GLIST", $gjs);
  

  // default visibility for new event

  $ic= 0; 
  $avis = CAL_getEventVisibilities($dbaccess, "");
  foreach ($avis as $k => $v) {
    if (count($igroups)==0 && $k==2) continue;
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = $v;
    $tconf[$ic]["selected"] = ($action->getParam("WGCAL_U_RVDEFCONF",0)==$k?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("evdefconf", $tconf);
  if ($action->getParam("WGCAL_U_RVDEFCONF",0)==2) {
    $action->lay->set("fshowgroupsd", "block");
  } else {
    $action->lay->set("fshowgroupsd", "none");
  }




  // Agenda visibility

  $action->lay->set("cginit", false);
  if (!$action->HasPermission("WGCAL_VCAL")) {

    $action->lay->set("showvcal", false);

  } else {

    $ucal = getUserPublicAgenda($duser->id, false);
    $action->lay->set("vcalid", $ucal->id);
    $action->lay->set("showgroups", false);
    $action->lay->set("showvcal", true);

    $vcalmode = $ucal->getValue("agd_vgroupmode");
    $vcalgrp  = $ucal->getTValue("agd_vgroupfid");
    $vcalgrpw  = $ucal->getTValue("agd_vgrouprw");
    $action->lay->set("allsel", ($vcalmode==1 ? false : true));
    $action->lay->set("vgroup", ($vcalmode==1 ? "block" : "none"));
    $action->lay->set("vcalmode", ($vcalmode==1 ? 1 : 0));
    $action->lay->set("vcalgroups", implode("|",$vcalgrp));

    // compute user real groups (user groups used in Agenda)
    $wgcal_groups = wGetGroups();
    $user_groups = $duser->getTValue("us_idgroup");
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

    $igroups = array(); $ig=0;
    if (count($u_rgroups)>0) {

      $action->lay->set("showgroups", true);
      $onesel = false;
      foreach ($u_rgroups as $k => $v) {
	$gr = new_Doc($dbaccess, $k);
	$igroups[$ig]["gfid"] = $gr->id;
	$igroups[$ig]["gid"] = $gr->getValue("us_whatid");
	$igroups[$ig]["gtitle"] = ucwords(strtolower($gr->title));
	$igroups[$ig]["gicon"] = $gr->GetIcon();
	$igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($gr->title)));
	$igroups[$ig]["gisused"] = $v["sel"];
	$igroups[$ig]["gwriteselected"] = ($v["wri"] ? "checked" : "");
	$igroups[$ig]["gwvisibility"] = ($v["sel"] ? "visible" : "hidden");
	if ($v["sel"]) $onesel = true;
	$ig++;
      }

      $gjs = array(); $igjs=0;
      foreach ($igroups as $k => $v) {
	if ($v["gisused"] || !$onesel) {
	  $gjs[$igjs]["igroup"] = $igjs;
	  $gjs[$igjs]["gfid"] = $v["gfid"];
	  $gjs[$igjs]["gmode"] = "true";
	  if (!$onesel) {
	    $igroups[$k]["gisused"] = true;
	    $igroups[$k]["gwriteselected"] = "checked";
	    $igroups[$k]["gwvisibility"] = "visible";
	  }
	  $igjs++;
	}    
      } 
      $action->lay->set("cginit", !$onesel);
    }
    $action->lay->setBlockData("cgroups", $igroups);
    $action->lay->setBlockData("GCLIST", $gjs);
  }
  return;

}
?>
