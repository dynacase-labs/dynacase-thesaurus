<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_look.php,v 1.1 2005/02/17 07:11:46 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("WGCAL/WGCAL_external.php");


function wgcal_prefs_look(&$action) {

  $themes = array(
		  array("name" => "default", "descr" => N_("the default theme")),
		  array("name" => "orange", "descr" => N_("orange")),
		  array("name" => "caroline", "descr" => N_("for the girls"))
		  );
  $opt = array(); $i = 0;
  foreach ($themes as $k => $v) {
    $opt[$i]["optvalue"] = $v["name"];
    $opt[$i]["optdescr"] = $v["descr"];
    $opt[$i]["optselect"] = ($v["name"]==$action->GetParam("WGCAL_U_THEME") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("THEME", $opt);

  $optchk = array(
		  "useicon" => array(N_("view icon in event"),"WGCAL_U_RESUMEICON", "wgcal_calendar", "WGCAL_CALENDAR")
		  );
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $toptchk = array(); 
  $io = 0;
  foreach ($optchk as $ko => $vo) {
    $toptchk[$io]["idoption"] = $ko;
    $toptchk[$io]["textoption"] = $vo[0];
    $toptchk[$io]["paramoption"] = $vo[1];
    $toptchk[$io]["trefresh"] = $vo[2];
    $toptchk[$io]["arefresh"] = $vo[3];
    $toptchk[$io]["stateoption"] = ($action->GetParam($vo[1]) == 1 ? "checked" : "");
    $io++;
  }
  $action->lay->SetBlockData("OPTCHK", $toptchk);

  $popuppos = array( "Float" => N_("floating, follows the pointer"),
		     "LeftTop" => N_("on the left top"), 
		     "LeftBottom" => N_("on the left bottom"), 
		     "RightTop" => N_("on the right top"), 
		     "RightBottom" => N_("on the right bottom") );
  $opt = array(); $i = 0;
  foreach ($popuppos as $k => $v) {
    $opt[$i]["optvalue"] = $k;
    $opt[$i]["optdescr"] = $v;
    $opt[$i]["optselect"] = ($k==$action->GetParam("WGCAL_U_ALTFIXED") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("ALTPOS", $opt);

  $popuptimer = array( "200" => N_("200 milli second"),
		       "500" => N_("1/2 second"), 
		       "1000" => N_("1 second"), 
		       "1500" => N_("1,5 second"));
  $opt = array(); $i = 0;
  foreach ($popuptimer as $k => $v) {
    $opt[$i]["optvalue"] = $k;
    $opt[$i]["optdescr"] = $v;
    $opt[$i]["optselect"] = ($k==$action->GetParam("WGCAL_U_ALTTIMER") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("ALTTIMER", $opt);

  $opt = array(); $i = 0;
  for ($i=0; $i<13; $i++) {
    $opt[$i]["optvalue"] = $i;
    $opt[$i]["optdescr"] = $i."H00";
    $opt[$i]["optselect"] = ($k==$action->GetParam("WGCAL_U_STARTHOUR") ? "selected" : "");
  }
  $action->lay->SetBlockData("SH", $opt);

   $opt = array(); $i = 0;
  for ($i=13; $i<24; $i++) {
    $opt[$i]["optvalue"] = $i;
    $opt[$i]["optdescr"] = $i."H00";
    $opt[$i]["optselect"] = ($k==$action->GetParam("WGCAL_U_ENDHOUR") ? "selected" : "");
  }
  $action->lay->SetBlockData("EH", $opt);
 
    
 
    
  return;
}
?>
