<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.34 2005/03/09 22:27:44 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

define("SEC_PER_DAY", 24*3600);
define("SEC_PER_HOUR", 3600);
define("SEC_PER_MIN", 60);

function printhdiv($h, $hdiv, $hd) {
  $sd = $h."H";
  $sh = "00";
  $sh = sprintf("%d",((60/$hdiv)*$hd));
  if (strlen($sh) == 1) $sh = "0".$sh;
  return $sd.$sh;
}

function d2s($t, $f="%x %X") {
  return strftime($f, $t);
}

function wgcal_calendar(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");


  $swe = $action->GetParam("WGCAL_U_VIEWWEEKEND", "yes");
  $dayperweek = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  if ($swe!="yes") {
    $ndays = $dayperweek - 2;
    $action->lay->set("wedisplayed", "" ); 
  } else {
    $ndays = $dayperweek;
    $action->lay->set("wedisplayed", "checked" );
  }
  $hcolsize = 5;
  $colsize = round((100 - $hcolsize) / $ndays);

  $sdate = WGCalGetDayFromTs($action->GetParam("WGCAL_U_CALCURDATE", time()));
  $cdate = WGCalGetDayFromTs(time());
  $firstWeekDay = WGCalGetFirstDayOfWeek($sdate);
  $edate = $firstWeekDay + ($ndays * SEC_PER_DAY) - 1;
  $pafter = $sdate + ($ndays * SEC_PER_DAY);
  $pbefore = $sdate - ($ndays * SEC_PER_DAY);

  $year  = strftime("%Y",$sdate);
  $month = strftime("%B",$sdate);
  $week  = strftime("%V",$sdate);
  $iday  = strftime("%u",$sdate);
  $day   = strftime("%d",$sdate);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv   = $action->GetParam("WGCAL_U_HOURDIV", 1);
  for ($h=0; $h<=3; $h++) {
    $tdiv[$h]["value"] = $h+1;
    $tdiv[$h]["descr"] = ($h==0?"1h":"1/".($h+1)."h");
    $tdiv[$h]["selected"] = ($hdiv==$h+1?"selected":"");
  }
  $action->lay->SetBlockData("CHHDIV", $tdiv);
  if ($hdiv>1) $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40) / ($hdiv - 1);
  else $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40);
  

  $action->lay->set("DIVSTART", "calareastart");
  $action->lay->set("DIVEND", "calareaend");
  
  $action->lay->set("colspan", $ndays+1 );
  $action->lay->set("week", $week);
  $action->lay->set("month", $month);
  $action->lay->set("year", $year);
  $action->lay->set("pafter", $pafter);
  $action->lay->set("pbefore", $pbefore);
  $action->lay->set("pcurrent", time());

  $action->lay->set("WEEKNUMBER", $week);
  $curday = -1;
  $tabdays = array(); $itd=0;
  for ($i=0; $i<$ndays; $i++) { 
    $tabdays[$i]["iday"] =  $i;
    $tabdays[$i]["days"] =  strftime("%s", $firstWeekDay+($i*SEC_PER_DAY));
    $tabdays[$i]["vstart"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*($hstart-1));
    $tabdays[$i]["vstartc"] =  strftime("%x %X", $tabdays[$i]["vstart"]);
    $tabdays[$i]["vend"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*$hstop) -1;
    if ($cdate==$tabdays[$i]["days"]) {
      $class[$i] = "WGCAL_DayCur";
      $classh[$i] = "WGCAL_DayLineCur";
      $curday = $i; 
    } else if ($sdate==$tabdays[$i]["days"]) {
      $classh[$i] = "WGCAL_DayLineCur";
      $class[$i] = "WGCAL_Day";
    } else {
      $classh[$i] = "WGCAL_Period"; 
      if ($i==5||$i==6) $class[$i] = "WGCAL_DayWE";
      else $class[$i] = "WGCAL_Day";
    }
    $t[$i]["IDD"] = $i;
    $t[$i]["colsize"] = $colsize;
    $t[$i]["CSS"] = $classh[$i];
    $t[$i]["LABEL"] = d2s($firstWeekDay+($i*SEC_PER_DAY), "%a %d %b");
  }
  $action->lay->SetBlockData("DAYS_LINE", $t);
  
  $urlroot = $action->GetParam("CORE_STANDURL");
  $lcell = new Layout( "WGCAL/Layout/wgcal-cellcalendar.xml", $action );
  $nl = 0;
  for ($h=$hstart-1; $h<=($hstop+1); $h++) {
    if ($h<$hstart || $h>$hstop) $ndiv = 1;
    else $ndiv = $hdiv;
    $mdiv = round(SEC_PER_HOUR/$ndiv);
    for ($hd=0; $hd<$ndiv; $hd++) {
      $thr[$nl]["LID"] = $nl;
      $thr[$nl]["HLINEHOURS"] = $hhight;
      $thr[$nl]["HCLASS"] = "WGCAL_DayHours";
      if ($h==($hstart-1) || $h==$hstop+1) 
	$thr[$nl]["HOURR"] = "";
      else if ($hd==0) {
	$thr[$nl]["HOURR"] = ($h==($hstart-1)?"":$h)."H00";
	$thr[$nl]["HCLASS"] = "WGCAL_DayHours";
      } else {
	$thr[$nl]["HOURR"] = printhdiv(($h==($hstart-1)?"":$h), $ndiv,$hd);
	$thr[$nl]["HCLASS"] = "WGCAL_DayMin";
      }
      $tcell = array();
      $itc = 0;
      for ($id=0; $id<$ndays; $id++) {
	if ($id>6) $mo = $id;
	else $mo = $id % 7;
	$tcell[$itc]["cellref"] = 'D'.$id.'H'.$nl;
	$tcell[$itc]["colsize"] = $colsize;
	$tcell[$itc]["urlroot"] = $urlroot;
	if ($h==($hstart-1)) $tcell[$itc]["nh"] = 1;
	else $tcell[$itc]["nh"] = 0;
	$tcell[$itc]["times"] = d2s($firstWeekDay+($id*SEC_PER_DAY)+($h*SEC_PER_HOUR)+ ($hd*$mdiv), "%s");
	$tcell[$itc]["timee"] = $tcell[$itc]["times"] + (($hd==0?1:$hd) * $mdiv);
	$tcell[$itc]["rtime"] = d2s($firstWeekDay+($id*SEC_PER_DAY), "%a %d %B %Y, ");
	$tcell[$itc]["rtime"] .= d2s($tcell[$itc]["times"],"%H:%M")." - ";
	$tcell[$itc]["rtime"] .= d2s($tcell[$itc]["timee"],"%H:%M");
	$tcell[$itc]["lref"] = "L".$nl;
	$tcell[$itc]["cref"] = "D".$id;
        if ($h<$hstart || $h>$hstop) $tcell[$itc]["cclass"] = "WGCAL_DayNoHours";
	else $tcell[$itc]["cclass"] = $class[$id];
	$tcell[$itc]["dayclass"] = $thr[$nl]["HCLASS"];
	$tcell[$itc]["hourclass"] = $classh[$id];
	$tcell[$itc]["cellcontent"] = "";
 	//$tcell[$itc]["cellcontent"] = $h."/".$hd." ".strftime("%H:%M:%S",$tcell[$itc]["times"])." " . strftime("%H:%M:%S",$tcell[$itc]["timee"]);
	$itc++;
      }
      $lcell->SetBlockData("CELLS", $tcell);
      $thr[$nl]["C_LINE"] =  $lcell->Gen();
      $nl++;
    }
  }

  $action->lay->SetBlockData("HOURS", $thr);
  $action->lay->SetBlockData("DAYS", $tabdays);
  
  $action->lay->set("DAYCOUNT", $ndays);
  $action->lay->set("HSTART", ($hstart - 1)); // Minutes
  $action->lay->set("HCOUNT", (($hstop - $hstart + 1) * $hdiv ) + 1 ); // Minutes
  $action->lay->set("HDIV", $hdiv); // Minutes
  $action->lay->set("YDURATION", (60/$hdiv) );
  $action->lay->set("IDSTART", "D0H0");
  $action->lay->set("IDSTOP", "D".($ndays-1)."H".($nl-1));
  $action->lay->set("ALTFIXED", $action->GetParam("WGCAL_U_ALTFIXED", "Float"));
  $action->lay->set("ALTTIMER", $action->GetParam("WGCAL_U_ALTTIMER", "500"));
  
  $action->lay->set("WGCAL_U_HLINETITLE", $action->GetParam("WGCAL_U_HLINETITLE", 20));
  $action->lay->set("WGCAL_U_HLINEHOURS", $action->GetParam("WGCAL_U_HLINEHOURS", 40));
  $action->lay->set("WGCAL_U_HCOLW", $action->GetParam("WGCAL_U_HCOLW", 20));

  $viewme=false;
  $ress = WGCalGetRessDisplayed($action);
  $events = array();
  $tr=array(); 
  $ire=0;
  foreach ($ress as $kr=>$vr) {
    if ($vr->id>0) $tr[$ire++] = $vr->id;
    if ($vr->id==$action->user->fid) $viewme=true;
  }
  if ($viewme) {
    $grp = WGCalGetRGroups($action, $action->user->id);
    foreach ($grp as $kr=>$vr) $tr[$ire++] = $vr;
  }
  $events = WGCalGetAgendaEvents( $action,
				  $tr, 
				  d2s($firstWeekDay, "%Y-%m-%d %H:%M:%S"),
				  d2s($edate, "%Y-%m-%d %H:%M:%S") );
  
  $action->lay->SetBlockData("EVENTS", $events);
  $action->lay->SetBlockData("EVENTSSC", $events);

}


?>
