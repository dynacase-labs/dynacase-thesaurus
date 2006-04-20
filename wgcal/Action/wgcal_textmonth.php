<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");
include_once("WGCAL/Lib.wTools.php");
include_once('FDL/popup_util.php');
include_once('WHAT/Lib.Common.php');

function wgcal_textmonth(&$action) 
{


  $td_height = 80;
  $title_len = 40;

  $dbaccess = $action->GetParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-fr.js");
  $action->parent->AddJsRef("jscalendar/Layout/calendar-setup.js");

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);

  $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $firstMonthDay  = WGCalGetFirstDayOfMonth($ctime);
  $firstDay = strftime("%u", $firstMonthDay);

  $month = strftime("%m", $ctime);
  $year  = strftime("%Y", $ctime);
  $lastday =  w_DaysInMonth($ctime);

  $prevmontht = $firstMonthDay-(24*3600);
  $prevmonth = strftime("%B", $prevmontht);
  $nextmontht =  (24*3600)+mktime(0,0,0, $month+1, 1, $year);
  $nextmonth = strftime("%B", $nextmontht);

  $prevyeart = mktime(0,0,0, $month, 1, $year-1);
  $prevyear = strftime("%Y", $prevyeart);

  $nextyeart = mktime(0,0,0, $month, 1, $year+1);
  $nextyear = strftime("%Y", $nextyeart);

  // Search all event for this month
  $hsep = "&nbsp;-&nbsp;";
  $d1 = "".$year."-".$month."-01 00:00:00";
  $d2 = "".$year."-".$month."-".$lastday." 23:59:59";
  $events = wGetEvents($d1, $d2);
  $popuplist = array();
  $tdays = array();
  foreach ($events as $k => $v) {

    $events[$k]["evt_title"] = stripSlashes($v["evt_title"]);
    $events[$k]["icolist"] = "";
    $events[$k]["Icons"] = null;

    $events[$k]["haveCat"] = ($v["evt_code"]==0?false:true);
    $events[$k]["isMeeting"] = ($v["bgColor"]==$v["topColor"]?false:true);
    $events[$k]["vInvite"] = false;
    if ($events[$k]["isMeeting"]  && $action->user->fid!=$v["evt_idcreator"]) {
      $dt = getTDoc($dbaccess, $v["evt_idcreator"]);
      $events[$k]["Invite"] = $dt["title"];
      $events[$k]["vInvite"] = true;
    }
    
    $events[$k]["showOwner"] = false;
    if (!$events[$k]["vInvite"] && $action->user->fid!=$v["evt_idcreator"]) {
      $dt = getTDoc($dbaccess, $v["evt_idcreator"]);
      $events[$k]["owner"] = $dt["title"];
      $events[$k]["showOwner"] = true;
    }
    
    if ($v["icons"]!="") {
      $it = explode(",", $v["icons"]);
      if (count($it)>0) {
	foreach ($it as $ki => $vi) $events[$k]["icolist"] .= "<img src=\"".str_replace("'","",$vi)."\">";
      }
    }

    $dstart = substr($v["evt_begdate"], 0, 2);
    $end = ($v["evfc_realenddate"] == "" ? $v["evt_enddate"] : $v["evfc_realenddate"]);
    $dend = substr($end, 0, 2);
    $hstart = substr($v["evt_begdate"],11,5);
    $hend = substr($end,11,5);
    for ($id=intval($dstart); $id<=intval($dend); $id++) {
      if (!is_array($tdays[$id]->events)) {
	$tdays[$id]->ecount = 0;
	$tdays[$id]->events = array();
      }
      if ($hstart==$hend && $hend=="00:00") {
	$hours = "("._("no hour").")";
 	$p = 0;
     } else if ($hstart=="00:00" && $hend=="23:59") {
	$hours = "("._("all the day _ short").")";
 	$p = 1;
     } else {
	if ($dend==$dstart) {
	  $hours = $hstart."$hsep".$hend;
	  $p = 4;
	} else { 
	  if ($id==$dstart) {
	    $hours = $hstart."$hsep..."; //" 24:00";
	    $p = 4;
	  } else if ($id==$dend) {
	    $hours = "...$hsep".$hend; // "00:00 ".$lend;
	    $p = 2;
	  } else {
	    $hours = "("._("all the day _ short").")";
	    $p = 1;
	  }
	}
      }
      $events[$k]["pound"] = $p;
      $events[$k]["hours"] = $hours;
      $tdays[$id]->events[$tdays[$id]->ecount] = $events[$k];
      $tdays[$id]->ecount++;
    }
  }
  $action->lay->setBlockData("CARDS", $events);

  $displayWE = ($action->GetParam("WGCAL_U_VIEWWEEKEND", "yes") == "yes" ? true : false);
  $dayperline  = ($displayWE ? 7 : 5);

  $h = new Layout("WGCAL/Layout/textevent.xml", $action );
  $startdisplay = false;
  $cday = 1;
  $action->lay->set("month",strftime("%B %Y",$ctime));
  $action->lay->set("prevmontht",$prevmontht);
  $action->lay->set("prevmonth",$prevmonth);
  $action->lay->set("nextmonth",$nextmonth);
  $action->lay->set("nextmontht",$nextmontht);
  $action->lay->set("prevyeart", $prevyeart);
  $action->lay->set("prevyear", $prevyear);
  $action->lay->set("nextyeart", $nextyeart);
  $action->lay->set("nextyear", $nextyear);
  $action->lay->set("titlespan",($dayperline-2));
  $action->lay->set("dayperline",$dayperline-1);
  $li = 0;
  $alldays = false;
  while (!$alldaysdone) {
    $hday[$li]["line"] = "";
    for ($co=0; $co<=$dayperline-1; $co++) {

      if ($firstDay-1==$co || ($li>0 && $firstDay>$co)) $startdisplay = true;

      if ($startdisplay && $cday<=$lastday) {
	
	$tscday = $firstMonthDay+(($cday-1)*24*3600);
	$dayinweek = strftime("%u",$tscday);
	while (!$displayWE && $dayinweek>=6) {
	  $cday++;
	  $tscday = $firstMonthDay+(($cday-1)*24*3600);
	  $dayinweek = strftime("%u",$tscday);
	}
	$daynum = strftime("%d",$tscday);
	$daylabel = strftime("%A",$tscday);
	$d = array();
	$h->set("daynum",$daynum);
	$h->set("timeb", $tscday + ($hstart)*3600);
	$h->set("timee", $tscday + ($hstart+1)*3600);
	$h->set("daylabel",$daylabel);
	if (is_array($tdays[$cday]->events)) usort($tdays[$cday]->events, cmpRv);
	$h->SetBlockData("HLine", $tdays[$cday]->events);
	$hday[$li]["line"] .= "<td onmouseover=\"closeAllMenu();\" height=\"".$td_height."px\" class=\"wMonthTextTD\">".$h->gen()."</td>";
	$cday++; 
      } else {
	$hday[$li]["line"] .= "<td onmouseover=\"closeAllMenu();\" height=\"".$td_height."px\" class=\"wMonthTextTDUnused\">&nbsp;</td>"; 
	if ($cday>$lastday) $alldaysdone = true;
      }
    }
    $li++;
  }
  $action->lay->SetBlockData("DLINE", $hday);
  popupGen(0);
    
  return;
}

function cmpEvents($e1, $e2) {
  return $e1["START"] > $e2["START"];
}
function cmpRv($e1, $e2) {
  if ($e1["pound"]==$e2["pound"] && $e2["pound"]>3) return strcmp($e1["hours"], $e2["hours"]);
  return $e1["pound"] > $e2["pound"];
}
?>
