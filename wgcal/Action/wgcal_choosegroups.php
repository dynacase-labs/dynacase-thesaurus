<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_choosegroups.php,v 1.1 2005/07/05 03:04:54 marc Exp $
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
  $igroups = array(); $ig=0;
  foreach ($glist as $kg => $vg) {
    if ($vg["atags"]=='R') {
      $igroups[$ig]["gfid"] = $vg["id"];
      $igroups[$ig]["gid"] = $vg["us_whatid"];
      $igroups[$ig]["gtitle"] = $vg["title"];
      $igroups[$ig]["gicon"] = Doc::GetIcon($vg["icon"]);
      $igroups[$ig]["gjstitle"] = addslashes($vg["title"]);
      $igroups[$ig]["gisused"] = wGroupIsUsed($vg["id"]);
     $ig++;
    }
  }
  $action->lay->setBlockData("groups", $igroups);
  
  return;
}



?>