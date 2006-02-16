<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker.php,v 1.18 2006/02/16 15:46:21 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once('WGCAL/Lib.wTools.php');

function wgcal_resspicker(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $wre = GetHttpVars("wre", 0);      // 1 : Only Write enabled calendar
  $action->lay->set("wre", $wre);

  $fiuser = getIdFromName($dbaccess, "IUSER");

  $contacts = $action->GetParam("WGCAL_U_PREFRESSOURCES", "");
  $tcontacts = explode("|", $contacts);
  if (count($tcontacts)>0) {
    foreach ($tcontacts as $kc => $vc) {
      if ($vc=="") continue;
      $rd = new_Doc($dbaccess, $vc);
      $tc[$kc]["ID"] = $rd->id;
      $tc[$kc]["ICON"] = $rd->GetIcon();
      $tc[$kc]["TITLE"] = addslashes($rd->title);
      if ($rd->fromid==$fiuser) {
        $tc[$kc]["STATE"] = EVST_NEW;
        $tc[$kc]["TSTATE"] = WGCalGetLabelState(EVST_NEW);
        $tc[$kc]["CSTATE"] = WGCalGetColorState(EVST_NEW);
      } else {
        $tc[$kc]["STATE"] = -1;
        $tc[$kc]["TSTATE"] = "";
        $tc[$kc]["CSTATE"] = "";
      }
    }
  }
  $action->lay->SetBlockData("PREFCONTACT", $tc);

  // Get classses used for ressource
  $rclass = wGetUsedFamilies();
  $i = 0;
  foreach ($rclass as $k => $v) {
    $t[$i]["FAMID"] = $v["id"];
    $t[$i]["FAMICON"] = $v["icon"];
    $t[$i]["FAMTITLE"] = addslashes(ucwords(strtolower($v["title"])));
    $t[$i]["FAMSEL"] = "false";
    $i++;
  }
  $action->lay->SetBlockData("FAMRESS", $t);
  $action->lay->SetBlockData("FAMRESSJS", $t);
  $action->lay->set("updt", $target);
}
?>
