<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker.php,v 1.4 2005/01/18 18:40:48 marc Exp $
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
  $dbfr = $action->GetParam("FREEDOM_DB");
  // Get classses used for ressource
  $rclass = CAL_getRessourceFamilies($dbfr);
  $i = 0;
  foreach ($rclass as $k => $v) {
    $df = new Doc($dbfr, $v);
    $t[$i]["FAMID"] = $df->id;
    $t[$i]["FAMICON"] = $df->GetIcon();
    $t[$i]["FAMTITLE"] = $df->title;
    $i++;
  }
  $action->lay->SetBlockData("FAMRESS", $t);
  $action->lay->set("updt", $target);
}