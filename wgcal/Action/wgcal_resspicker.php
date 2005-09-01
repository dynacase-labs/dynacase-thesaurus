<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker.php,v 1.14 2005/09/01 16:48:27 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('EXTERNALS/WGCAL_external.php');

function wgcal_resspicker(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $wre = GetHttpVars("wre", 0);      // 1 : Only Write enabled calendar
  $action->lay->set("wre", $wre);

  $contacts = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
  $tcontacts = explode("|", $contacts);
  if (count($tcontacts)>0) {
    foreach ($tcontacts as $kc => $vc) {
      if ($vc=="") continue;
      $rd = new Doc($dbaccess, $vc);
      $tc[$kc]["ID"] = $rd->id;
      $tc[$kc]["ICON"] = $rd->GetIcon();
      $tc[$kc]["TITLE"] = addslashes($rd->title);
      $tc[$kc]["STATE"] = EVST_NEW;
      $tc[$kc]["TSTATE"] = WGCalGetLabelState(EVST_NEW);
      $tc[$kc]["CSTATE"] = WGCalGetColorState(EVST_NEW);
    }
  }
  $action->lay->SetBlockData("PREFCONTACT", $tc);

  // Get classses used for ressource
  $rclass = WGCalGetRessourceFamilies($dbaccess);
  $i = 0;
  $df = new Doc($dbaccess);
  foreach ($rclass as $k => $v) {
    $t[$i]["FAMID"] = $v["id"];
    $t[$i]["FAMICON"] = $df->GetIcon($v["icon"]);
    $t[$i]["FAMTITLE"] = addslashes($v["title"]);
    $t[$i]["FAMSEL"] = "false";
    $i++;
  }

  // Add statics calendars 
  //   $t[$i]["FAMID"] = getIdFromName($dbaccess,"SCALENDAR");
  //   $df = new Doc($dbaccess, $t[$i]["FAMID"]);
  //   $t[$i]["FAMICON"] = $df->GetIcon();
  //   $t[$i]["FAMTITLE"] = _("my calendars");
  //   $t[$i]["FAMSEL"] = "false";

  $action->lay->SetBlockData("FAMRESS", $t);
  $action->lay->SetBlockData("FAMRESSJS", $t);
  $action->lay->set("updt", $target);
}
?>
