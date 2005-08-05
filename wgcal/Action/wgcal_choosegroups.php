<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_choosegroups.php,v 1.2 2005/08/05 15:24:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('FDL/Class.Doc.php');
include_once('WGCAL/Lib.wTools.php');

function wgcal_choosegroups(&$action) {


  $dbaccess = $action->getParam("FREEDOM_DB");
  $udbaccess = $action->GetParam("COREUSER_DB");

  $igroupfid = getIdFromName($dbaccess, "IGROUP");
  $glist = GetChildDoc($dbaccess, 0, 0, "ALL", array(), $action->user->id, "TABLE", $igroupfid);
  $gjs = array(); $igjs=0;
  $igroups = array(); $ig=0;
  foreach ($glist as $kg => $vg) {
    $igroups[$ig]["gfid"] = $vg["id"];
    $igroups[$ig]["gid"] = $vg["us_whatid"];
    $igroups[$ig]["gtitle"] = ucwords(strtolower($vg["title"]));
    $igroups[$ig]["gicon"] = Doc::GetIcon($vg["icon"]);
    $igroups[$ig]["gjstitle"] = addslashes(ucwords(strtolower($vg["title"])));
    $igroups[$ig]["gisused"] = wGroupIsUsed($vg["id"]);
    if ($igroups[$ig]["gisused"]) {
      $gjs[$igjs]["igroup"] = $igjs;
      $gjs[$igjs]["gfid"] = $vg["id"];
      $igjs++;
    }
    $ig++;
  }
  $action->lay->setBlockData("GLIST", $gjs);
  $action->lay->setBlockData("groups", $igroups);
  
  return;
}



?>