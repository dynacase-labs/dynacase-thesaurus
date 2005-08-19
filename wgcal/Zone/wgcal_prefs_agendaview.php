<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_agendaview.php,v 1.1 2005/08/19 17:22:03 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once('WGCAL/Lib.wTools.php');
include_once('FDL/Class.Doc.php');


function wgcal_prefs_agendaview(&$action) {

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $uid = GetHttpVars("uid", $action->user->id);
  
  if (!$action->HasPermission("WGCAL_VCAL")) {

    $action->lay->set("showvcal", false);

  } else {

    $action->lay->set("showvcal", true);
    $user = new Doc($dbaccess, $action->user->fid);

    $vcalmode = $user->getValue("us_wgcal_vcalgrpmode");
    $vcalgrp  = $user->getTValue("us_wgcal_vcalgrpid");
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
      }
    }

    foreach ($vcalgrp as $kg => $vg) {
      if (isset($u_rgroups[$vg])) $u_rgroups[$vg]["sel"] = true;
    }

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
  }
  return;

}