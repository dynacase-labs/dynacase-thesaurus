<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker.php,v 1.5 2005/01/31 10:55:26 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('WGCAL/WGCAL_external.php');

function wgcal_resspicker(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  // Get classses used for ressource
  $rclass = WGCalGetRessourceFamilies($dbaccess);
  $i = 0;
  $df = new Doc($dbaccess);
  foreach ($rclass as $k => $v) {
    $t[$i]["FAMID"] = $v["id"];
    $t[$i]["FAMICON"] = $df->GetIcon($v["icon"]);
    $t[$i]["FAMTITLE"] = $v["title"];
    $i++;
  }
  $action->lay->SetBlockData("FAMRESS", $t);
  $action->lay->set("updt", $target);
}