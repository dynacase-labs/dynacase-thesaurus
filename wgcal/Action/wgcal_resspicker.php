<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker.php,v 1.8 2005/02/08 11:32:24 marc Exp $
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

  // Add statics calendars 
  $t[$i]["FAMID"] = getIdFromName($dbaccess,"SCALENDAR");
  $df = new Doc($dbaccess, $t[$i]["FAMID"]);
  $t[$i]["FAMICON"] = $df->GetIcon();
  $t[$i]["FAMTITLE"] = _("my calendars");

  $action->lay->SetBlockData("FAMRESS", $t);
  $action->lay->set("updt", $target);
}
?>
