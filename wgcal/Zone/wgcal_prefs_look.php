<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_prefs_look.php,v 1.17 2005/12/08 15:57:33 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */
include_once('FDL/Lib.Dir.php');
include_once("EXTERNALS/WGCAL_external.php");
include_once("WGCAL/Lib.wTools.php");


function wgcal_prefs_look(&$action) {
  

  $uid = GetHttpVars("uid", $action->user->id);
  $action->lay->set("uid", $uid);

  $zwrvs = array( 30 => _("small"), 40 => _("medium"), 50 => _("large"));
  $opt = array(); $i = 0;
  foreach ($zwrvs as $k => $v) {
    $opt[$i]["optvalue"] = $k;
    $opt[$i]["optdescr"] = $v;
    $opt[$i]["optselect"] = ($k==$action->GetParam("WGCAL_U_HLINEHOURS") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("HDIVSZ", $opt);

  // Themes ----------------------------------------------------------
  $themes = array();
  $themedir = "WGCAL/Themes";
  $ith=0;
  $list = GetFilesByExt($themedir, ".thm"); 
  foreach ($list as $k => $v) {
    include_once($themedir."/".$v.".thm");
    $themes[$ith]["name"] = $v;
    $themes[$ith++]["descr"] = $theme->Descr;
  }
  if (count($themes)<1) $themes[0] = array("name" => "default", "descr" => _("the default theme"));


  $opt = array(); $i = 0;
  foreach ($themes as $k => $v) {
    $opt[$i]["optvalue"] = $v["name"];
    $opt[$i]["optdescr"] = $v["descr"];
    $opt[$i]["optselect"] = ($v["name"]==$action->GetParam("WGCAL_U_THEME") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("THEME", $opt);
 
  // Font sizing ----------------------------------------------------------
  $fontsz = array();
  $fontszdir = "WGCAL/Themes";
  $ith=0;
  $list = GetFilesByExt($themedir, ".fsz");
  foreach ($list as $k => $v) {
    include_once($fontszdir."/".$v.".fsz");
    $fontsz[$ith]["name"] = $v;
    $fontsz[$ith++]["descr"] = _($v);
  }
  if (count($fontsz)<1) $fontsz[0] = array("name" => "normal", "descr" => "normal size");

  $opt = array(); $i = 0;
  foreach ($fontsz as $k => $v) {
    $opt[$i]["optvalue"] = $v["name"];
    $opt[$i]["optdescr"] = $v["descr"];
    $opt[$i]["optselect"] = ($v["name"]==$action->GetParam("WGCAL_U_FONTSZ") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("FONTSZ", $opt);

  // Display icons in events ----------------------------------------------------------
  $optchk = array(
		  "useicon" => array(_("view icon in event"),"WGCAL_U_RESUMEICON", "wgcal_calendar", "WGCAL_CALENDAR")
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

  $popuppos = array( "Float" => _("floating, follows the pointer"),
		     "LeftTop" => _("on the left top"), 
		     "LeftBottom" => _("on the left bottom"), 
		     "RightTop" => _("on the right top"), 
		     "RightBottom" => _("on the right bottom") );
  $opt = array(); $i = 0;
  foreach ($popuppos as $k => $v) {
    $opt[$i]["optvalue"] = $k;
    $opt[$i]["optdescr"] = $v;
    $opt[$i]["optselect"] = ($k==$action->GetParam("WGCAL_U_ALTFIXED") ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("ALTPOS", $opt);

  $popuptimer = array( "200" => _("200 milli second"),
		       "500" => _("1/2 second"), 
		       "1000" => _("1 second"), 
		       "1500" => _("1,5 second"));
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
    $opt[$i]["optselect"] = ($i==$action->GetParam("WGCAL_U_STARTHOUR") ? "selected" : "");
  }
  $action->lay->SetBlockData("SH", $opt);

  $opt = array(); $i = 0;
  for ($i=13; $i<24; $i++) {
    $opt[$i]["optvalue"] = $i;
    $opt[$i]["optdescr"] = $i."H00";
    $opt[$i]["optselect"] = ($i==$action->GetParam("WGCAL_U_STOPHOUR") ? "selected" : "");
  }
  $action->lay->SetBlockData("EH", $opt);
 
    
  $opt = array(); $i = 0;
  for ($i=0; $i<=23; $i++) {
    $opt[$i]["optvalue"] = $i;
    $opt[$i]["optdescr"] = $i."H";
    $opt[$i]["optselect"] = ($i==$action->GetParam("WGCAL_U_HSUSED",7) ? "selected" : "");
  }
  $action->lay->SetBlockData("HSUSED", $opt);
  $opt = array(); $i = 0;
  for ($i=0; $i<=23; $i++) {
    $opt[$i]["optvalue"] = $i;
    $opt[$i]["optdescr"] = $i."H";
    $opt[$i]["optselect"] = ($i==$action->GetParam("WGCAL_U_HEUSED",  23) ? "selected" : "");
  }
  $action->lay->SetBlockData("HEUSED", $opt);

  $opt = array(); $i = 0;
  $minc = array( "1/4 h" => 15, "1/2 h" => 30, "1 h" => 60);
  foreach ($minc as $k => $v) { 
    $opt[$i]["optvalue"] = $v;
    $opt[$i]["optdescr"] = $k;
     $opt[$i]["optselect"] = ($v==$action->getParam("WGCAL_U_RVDEFDUR", 60) ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("RVDEFDUR", $opt);

  $opt = array(); $i = 0;
  $minc = array( "2","5","10","15","20","25","30","40","45"); 
  foreach ($minc as $k => $v) {
    $opt[$i]["optvalue"] = $v;
    $opt[$i]["optdescr"] = $v." min.";
    $opt[$i]["optselect"] = ($v==$action->GetParam("WGCAL_U_MINCUSED",15) ? "selected" : "");
    $i++;
  }
  $action->lay->SetBlockData("MINCUSED", $opt);
 
    
 
    
  return;
}
?>
