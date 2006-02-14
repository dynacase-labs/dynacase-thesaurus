<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.65 2006/02/14 10:14:31 marc Exp $
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


  $dbaccess = $action->GetParam("FREEDOM_DB");

  $ress = GetHttpVars("ress", "");
  setHttpVar("ress", $ress);

  // Check for standalone mode 
  $sm = (GetHttpVars("sm", 0) == 0 ? false : true);
  
//   // Init start time, view mode (month, week, ...)
  $vm = GetHttpVars("vm", "");
  if ($vm=="" || !is_numeric($vm)) $vm = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  $dayperweek = $vm;
  if ($dayperweek==-1) redirect($action,"WGCAL","WGCAL_TEXTMONTH");

  $swe = $action->GetParam("WGCAL_U_VIEWWEEKEND", "yes");
  if ($swe!="yes") $ndays = $dayperweek - 2;
  else $ndays = $dayperweek;

  $ts = GetHttpVars("ts", 0);
  $stdate = $ts;
  if ($stdate == 0) $stdate = $action->GetParam("WGCAL_U_CALCURDATE", time());
  if (!$sm) $action->parent->param->set("WGCAL_U_CALCURDATE", $stdate, PARAM_USER.$action->user->id, $action->parent->id);
  $sdate = w_GetDayFromTs($stdate); 
  $firstWeekDay = w_GetFirstDayOfWeek($sdate);
  $edate = $firstWeekDay + ($ndays * SEC_PER_DAY) - 1;

  $tout = wGetEvents(ts2db($firstWeekDay, "Y-m-d H:i:s"), ts2db($edate, "Y-m-d H:i:s"));
  $popuplist = array();
  foreach ($tout as $k => $v) {
    $IsRV = ($v["FIDP"]==getIdFromName($dbaccess,"CALEVENT") ? true : false);
    $d = new_Doc($dbaccess, $v["IDP"]);  
    $tout[$k]["EvPCard"] = $d->viewDoc($d->defaultview);
    $tout[$k]["EvRCard"] = $d->viewDoc(($d->defaultabstract=="FDL:VIEWABSTRACTCARD")?"FDL:VIEWTHUMBCARD":$d->defaultabstract);
    if ($IsRV) {
      $tout[$k]["vRv"] = true;
      $tout[$k]["edit"] = ($d->Control("edit")==""?true:false);
      if ($tout[$k]["edit"]) $tout[$k]["vRv"] = false;
      if (!isset($popuplist[$d->popup_name])) {
	$popuplist[$d->popup_name] = true;
	popupInit($d->popup_name,  $d->popup_item);
      }
      $d->RvSetPopup($k);
    } else {
      $tout[$k]["edit"] = false;
      $tout[$k]["vRv"] = false;
    }
  }
  popupGen(count($tout));

  // Display results ------------------------------------------------------------------------------------

  $action->lay->set("sm", $sm);
  $action->lay->set("vm", $vm);
  $action->lay->set("ts", $ts);
  $action->lay->set("ress", $ress);

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $action->lay->set("standAlone", $sm);

  $Hcolsize = 5;
  $action->lay->set("hcolsize", $Hcolsize);
  $colsize = floor((100-$Hcolsize) / ($ndays));

  $cdate = w_GetDayFromTs(time());
  $pafter = $sdate + ($ndays * SEC_PER_DAY);
  $pbefore = $sdate - ($ndays * SEC_PER_DAY);

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
  $eweek  = strftime("%V",$edate);
  $action->lay->set("plusweek", "");
  if ($week!=$eweek) {
        $week = $week." - ".$eweek;
        $action->lay->set("plusweek", "s");
  }
  $iday  = strftime("%u",$firstWeekDay);
  $day   = strftime("%d",$firstWeekDay);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv   = $action->GetParam("WGCAL_U_HOURDIV", 1);
  if ($hdiv>1) $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40) / ($hdiv - 1);
  else $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40);
  
  $action->lay->set("colspan", $ndays+1 );
  $action->lay->set("week", $week);
  $action->lay->set("month", ucwords($strmonth));
  $action->lay->set("pafter", $pafter);
  $action->lay->set("pbefore", $pbefore);
  $action->lay->set("pcurrent", time());

  $tabdays = array(); $itd=0;
  for ($i=0; $i<$ndays; $i++) { 
    $tabdays[$i]["iday"] =  $i;
    $tabdays[$i]["days"] =  strftime("%s", $firstWeekDay+($i*SEC_PER_DAY));
    $tabdays[$i]["vstart"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*$hstart - (3600/$hdiv));
    $tabdays[$i]["vend"] =  $tabdays[$i]["days"] + (SEC_PER_HOUR*$hstop + 3600 + (3600/$hdiv));
    $class[$i] = "WGCAL_Day";
    $classh[$i] = "WGCAL_DayLine"; 
    if (strftime("%Y%m%d", $firstWeekDay+($i*SEC_PER_DAY)) == strftime("%Y%m%d", time())) {
      $class[$i] .= " WGCAL_DayCur";
    } else {
      $iwe = $i % 7;
      if ($iwe==5 || $iwe==6) $class[$i] .= " WGCAL_DayWE";
    }      
    $t[$i]["IDD"] = $i;
    $t[$i]["colsize"] = $colsize;
    $t[$i]["CSS"] = $classh[$i];
    $t[$i]["LABEL1"] = ucwords(strftime("%a %d", $firstWeekDay+($i*SEC_PER_DAY)));
    $t[$i]["times"] = $tabdays[$i]["vstart"] ;
    $t[$i]["timee"] = $t[$i]["times"] +  SEC_PER_HOUR;
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
	$tcell[$itc]["cellcontent"] = "";
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
//   $action->lay->set("HSTART", ($hstart - 1)); 
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
  $action->lay->SetBlockData("EVENTSSC", $tout);

}


function printhdiv($h, $hdiv, $hd) {
  $sd = $h."H";
  $sh = "00";
  $sh = sprintf("%d",((60/$hdiv)*$hd));
  if (strlen($sh) == 1) $sh = "0".$sh;
  return $sd.$sh;
}

?>
