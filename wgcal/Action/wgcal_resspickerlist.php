<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_resspickerlist.php,v 1.1 2004/12/08 16:44:18 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');

function wgcal_resspickerlist(&$action) {

  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $target = GetHttpVars("updt", "");
  if ($target == "") {
    echo "<h2> Missing form to update ! </h2>";
    return;
  }
  $action->lay->set("updt", $target);

  $families = GetHttpVars("sfam", "");
  $title    = GetHttpVars("stext", "");

  if ($families == "") return;
  //if ($title == "") return;

  $filter = array( );
  $if = 0;
  if ($title!="") $filter[$if++] = "title ~* '".$title."'";
  $fam = explode("|", $families);
  $ffam = "";
  foreach ($fam as $k => $v) {
    if ($v!="") {
      $ffam .= ($ffam==""?"":" or ");
      $ffam .= "fromid = $v";
    }
  }
  if ($ffam!="") $filter[$if++] = "( ".$ffam." ) ";
  $rdoc = GetChildDoc($action->GetParam("FREEDOM_DB"), 0, 0, "ALL", $filter, $action->user->id);
  $i = 0;
  foreach ($rdoc as $k => $v) {
    $t[$i]["RESSID"] = $v->id;
    $t[$i]["RESSICON"] = $v->GetIcon();
    $t[$i]["RESSTITLE"] = $v->title;
    $i++;
  }
  $action->lay->SetBlockData("RESSOURCES", $t);
}
