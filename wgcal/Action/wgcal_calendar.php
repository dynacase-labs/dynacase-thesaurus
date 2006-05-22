<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.93 2006/05/22 14:30:11 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.wTools.php");
include_once('FDL/popup_util.php');
include_once('WHAT/Lib.Common.php');

function wgcal_calendar(&$action) {

  if ($dayperweek==-1) redirect($action,"WGCAL","WGCAL_TEXTMONTH");

  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("FDL/Layout/iframe.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $action->parent->AddJsRef("FDL/Layout/popupdoc.js");  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->parent->AddCssRef("FDL:POPUP.CSS",true);

  $ress = GetHttpVars("ress", "");
  setHttpVar("ress", $ress);
  
  // Check for standalone mode 
  $sm = (GetHttpVars("sm", 0) == 0 ? false : true);
  if ($sm) {
    $tr = explode("|", $ress);
    $atitle = "";
    foreach ($tr as $k => $v) {
      $ud = GetTDoc($dbaccess, $v);
      $atitle .= ($atitle==""?"":", ").ucwords(strtolower($ud["title"]));
    }
    $action->lay->set("agendatitle", $atitle); 
  }
    
  //   // Init start time, view mode (month, week, ...)
  $vm = GetHttpVars("vm", "");
  if ($vm=="" || !is_numeric($vm)) $vm = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  $dayperweek = $vm;
  if ($dayperweek==-1) redirect($action,"WGCAL","WGCAL_TEXTMONTH");
  
  popupGen(0);

  $swe = $action->GetParam("WGCAL_U_VIEWWEEKEND", "yes");
  if ($swe!="yes") {
    $vd = floor(($dayperweek) / 7);
    $ndays = $dayperweek - (2 * $vd);
  } else $ndays = $dayperweek;
  $ts = GetHttpVars("ts", 0);
  $stdate = $ts;
  if ($stdate == 0) $stdate = $action->GetParam("WGCAL_U_CALCURDATE", time());
  if (!$sm) $action->parent->param->set("WGCAL_U_CALCURDATE", $stdate, PARAM_USER.$action->user->id, $action->parent->id);
  $sdate = w_GetDayFromTs($stdate); 
  $firstWeekDay = w_GetFirstDayOfWeek($sdate);
  $edate = $firstWeekDay + ($dayperweek * SEC_PER_DAY) - 1;
  $d1 = ts2db($firstWeekDay, "Y-m-d 00:00:00");
  $d2 = ts2db($edate, "Y-m-d 23:59:59");


  $tout = wGetEvents($d1, $d2, true, $filter, "EVENT_FROM_CAL");
  
  // Display results ------------------------------------------------------------------------------------
  $action->lay->set("sm", $sm);
  $action->lay->set("vm", $vm);
  $action->lay->set("ts", $ts);
  $action->lay->set("ress", $ress);

  // Init slidder
  setHttpVar("sliddate", $stdate);
  setHttpVar("slidurl", $action->getParam("CORE_STANDURL")."&app=WGCAL&action=WGCAL_CALENDAR&ts=%TS%&sm=$sm&vm=$vm&ress=$ress");
  
  $action->lay->set("standAlone", $sm);
  
  $Hcolsize = 5;
  $action->lay->set("hcolsize", $Hcolsize);
  $action->lay->set("acolsize", floor(100-$Hcolsize));
  $colsize = floor((100-$Hcolsize) / ($ndays));
  
  $cdate = w_GetDayFromTs(time());
  $pafter = $sdate + ($dayperweek * SEC_PER_DAY);
  $pbefore = $sdate - ($dayperweek * SEC_PER_DAY);
  
  $year  = strftime("%Y",$firstWeekDay);
  $month = strftime("%B",$firstWeekDay);
  $emonth = strftime("%B",$edate);
  $eyear = strftime("%Y",$edate);
  if ($eyear!=$year) {
    $strmonth = $month." ".$year.", ".$emonth." ".$eyear;
  } else if ($month!=$emonth) {
    $strmonth = $month.", ".$emonth." ".$year;
  } else {
    $strmonth = $month." ".$year;
  }
  $week  = strftime("%V",$firstWeekDay);
  $plusweek = "";
  if ($dayperweek>7) {
    $week = $week."/".($week + 1);
    $plusweek = "s";
  }
//   setHttpVar("slidtitle", _("week").$plusweek." $week $strmonth");
   setHttpVar("slidtitle", ucwords($strmonth));

  $iday  = gmdate("w",$firstWeekDay);
  $day   = gmdate("d",$firstWeekDay);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv   = $action->GetParam("WGCAL_U_HOURDIV", 1);
  if ($hdiv>1) $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40) / ($hdiv - 1);
  else $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40);
  
  $action->lay->set("ncolspan", $ndays );
  $action->lay->set("colspan", $ndays+1 );
  $action->lay->set("week", $week);
  $action->lay->set("plusweek", $plusweek);
  $action->lay->set("month", ucwords($strmonth));
  $action->lay->set("pafter", $pafter);
  $action->lay->set("pbefore", $pbefore);
  $action->lay->set("pcurrent", time());
  
  $tabdays = array(); $itd=0;
  for ($i=0; $i<$dayperweek; $i++) { 

    $numd = strftime("%u",$firstWeekDay+($i*SEC_PER_DAY));
    
    $tabdays[$i]["iday"] =  $i;
    $tabdays[$i]["days"] =  $firstWeekDay+($i*SEC_PER_DAY);
    $tabdays[$i]["vstart"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*$hstart - (3600/$hdiv));
    $tabdays[$i]["vend"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*$hstop + 3600 + (3600/$hdiv));
    $tabdays[$i]["view"] =  ($numd>=6 && $swe!="yes" ? "false" : "true");
    $tabdays[$i]["iswe"] =  ($numd>=6 ? "true" : "false");
    $class[$i] = "WGCAL_Day";
    $classh[$i] = "WGCAL_DayLine"; 
    if (strftime("%Y%m%d", $firstWeekDay+($i*SEC_PER_DAY)) == strftime("%Y%m%d", time())) {
      $class[$i] = "WGCAL_DayCur";
    } else {
      $iwe = $i % 7;
      if ($iwe==5 || $iwe==6) $class[$i] = "WGCAL_DayWE";
      if ($iwe==0) $class[$i] = "WGCAL_DayMonday";
    }   
    if ($tabdays[$i]["view"] == "true") {
      $t[$i]["IDD"] = $i;
      $t[$i]["colsize"] = $colsize;
      $t[$i]["CSS"] = $classh[$i];
      $t[$i]["LABEL1"] = ucwords(strftime("%a %d", $firstWeekDay+($i*SEC_PER_DAY)));
      $t[$i]["times"] = $tabdays[$i]["vstart"] ;
      $t[$i]["timee"] = $tabdays[$i]["vstart"] +  SEC_PER_HOUR;
    }
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
	$thr[$nl]["HOURR"] = "&nbsp;";
      else if ($hd==0) {
	$thr[$nl]["HOURR"] = ($h==($hstart-1)?"":$h)."H00";
      } else {
	$thr[$nl]["HOURR"] = printhdiv(($h==($hstart-1)?"":$h), $ndiv,$hd);
	$thr[$nl]["HCLASS"] = "WGCAL_DayMin";
      }
      $tcell = array();

      $itc = 0;
      for ($id=0; $id<$dayperweek; $id++) {
	$numd = strftime("%u",$firstWeekDay+($id*SEC_PER_DAY));
	if ($numd>=6 && $swe!="yes") continue;
	if ($id>6) $mo = $id;
	else $mo = $id % 7;
	$tcell[$itc]["cellref"] = 'D'.$id.'H'.$nl;
	$tcell[$itc]["colsize"] = ($i==0?$Hcolsize:$colsize);
 	$tcell[$itc]["urlroot"] = $urlroot;
	$tcell[$itc]["times"] = $firstWeekDay + ($id*SEC_PER_DAY)+($h*SEC_PER_HOUR) + ($hd*$mdiv);
	$tcell[$itc]["timee"] = $tcell[$itc]["times"] + ($action->getParam("WGCAL_U_RVDEFDUR", 60)*60);
	$tcell[$itc]["rtime"] = w_strftime($firstWeekDay+($id*SEC_PER_DAY), WD_FMT_DAYLTEXT);
 	if ($h==($hstart-1) || $h==($hstop+1)) {
	  $tcell[$itc]["nh"] = 1;
	  $tcell[$itc]["rtime"] .= " "._("no hour");
	} else {
	  $tcell[$itc]["nh"] = 0;
	  $tcell[$itc]["rtime"] .= ", ".ts2db($tcell[$itc]["times"],"H:i")." - ";
	  $tcell[$itc]["rtime"] .= ts2db($tcell[$itc]["timee"],"H:i");
	}
	$tcell[$itc]["lref"] = "L".$nl;
	$tcell[$itc]["cref"] = "D".$id;
        if ($h<$hstart || $h>$hstop) $tcell[$itc]["cclass"] = "WGCAL_DayNoHours";
	else $tcell[$itc]["cclass"] = $class[$id];
	$tcell[$itc]["dayclass"] = $thr[$nl]["HCLASS"];
	$tcell[$itc]["hourclass"] = $classh[$id];
  	$tcell[$itc]["cellcontent"] = "&nbsp;"; //  $tcell[$itc]["cellref"];
	$itc++;
      }
      $lcell->SetBlockData("CELLS", $tcell);
      $thr[$nl]["C_LINE"] =  $lcell->Gen();
      $nl++;
    }
  }

  $action->lay->SetBlockData("HOURS", $thr);
  $action->lay->SetBlockData("DAYS", $tabdays);
  
  $action->lay->set("DAYCOUNT", $dayperweek);
  $action->lay->set("HSTART", ($hstart )); // Minutes
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

  $action->lay->SetBlockData("EVENTS", $tout);

  setThemeValue();


  // Fast edit zone
  $ag = "";
  $uagenda = getParam("WGCAL_U_AGENDASELECTED", $action->user->fid);
  if ($uagenda!=$action->user->fid) {
    $ag = "(".getUserAgenda($uagenda).")";
  }
  $action->lay->set("agendaowner", $ag );
  $catg = wGetCategories();
  $tcat = array(); 
  foreach ($catg as $k => $v) $tcat[] = array( "fe_catv" => $v["id"], "fe_catt" =>  ucwords(strtolower($v["label"]))); 
  $action->lay->setBlockData("category", $tcat);


  $defvis = $vis = getParam("WGCAL_U_RVDEFCONF",0);
  $avis = CAL_getEventVisibilities($dbaccess, "");
  $ic = 0;
  foreach ($avis as $k => $v) {
    if ($none && $k==2) continue;
    $tconf[$ic]["value"] = $k;
    $tconf[$ic]["descr"] = ucwords(strtolower($v));
    $tconf[$ic]["selected"] = ($vis==$k?"selected":"");
    $ic++;
  }
  $action->lay->SetBlockData("confid", $tconf);
  $action->lay->set("defvis", $defvis);


  // minutes
  $incm = $action->getParam("WGCAL_U_MINCUSED",15);
  for ($h=0; $h<60; $h+=$incm) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optselect"] = "";
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h);
  }
  $action->lay->setBlockData("ms_select", $th);
  $action->lay->setBlockData("me_select", $th);
  
  // hours
  $th = array();
  for ($h=0; $h<24; $h++) {
    $th[$h]["optvalue"] = $h;
    $th[$h]["optdescr"] = (strlen($h)==1?"0".$h:$h)."h";
    $th[$h]["optselect"] = "";
  }
  $action->lay->setBlockData("hs_select", $th);
  $action->lay->setBlockData("he_select", $th);

  
}


function printhdiv($h, $hdiv, $hd) {
  $sd = $h."H";
  $sh = "00";
  $sh = sprintf("%d",((60/$hdiv)*$hd));
  if (strlen($sh) == 1) $sh = "0".$sh;
  return $sd.$sh;
}

?>
