<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspicker.php,v 1.2 2004/12/09 17:30:17 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');

function wgcal_resspicker(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  // Get classses used for ressource
  $doc = new Doc($action->GetParam("FREEDOM_DB"));
  $filter = array( "doctype = 'C'", "usefor = 'R'" );
  $rclass = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, "ALL", $filter, $action->user->id, "TABLE");
  $i = 0;
  foreach ($rclass as $k => $v) {
    $t[$i]["FAMID"] = $v["id"];
    $t[$i]["FAMICON"] = $doc->GetIcon($v["icon"]);
    $t[$i]["FAMTITLE"] = $v["title"];
    $i++;
  }
  $action->lay->SetBlockData("FAMRESS", $t);
  $action->lay->set("updt", $target);
}