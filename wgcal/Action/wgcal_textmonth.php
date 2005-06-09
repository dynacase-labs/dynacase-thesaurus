<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_textmonth(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WHAT/Layout/geometry.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);

  $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $firstMonthDay  = WGCalGetFirstDayOfMonth($ctime);
  $firstDay = strftime("%u", $firstMonthDay);
  $month = strftime("%m", $ctime);
  $year  = strftime("%Y", $ctime);
  $lastday =  WGCalDaysInMonth($ctime);
  $prevmontht = $firstMonthDay-(24*3600);
  $prevmonth = strftime("%B", $prevmontht);
  $nextmontht =  (24*3600)+mktime(0,0,0, $month+1, 1, $year);
  $nextmonth = strftime("%B", $nextmontht);

  // Search all event for this month
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
  $d1 = "".$year."-".$month."-01 00:00:00";
  $d2 = "".$year."-".$month."-".$lastday." 23:59:59";
  $tevents = WGCalGetAgendaEvents($action, $tr, $d1, $d2);

  $action->lay->setBlockData("CARDS", $tevents);

  $tdays = array();
  foreach ($tevents as $ke => $ve) {
    $ev = new Doc($dbaccess, $ve["IDP"]);
    $dstart = substr($ve["TSSTART"], 0, 2);
    $dend = substr($ve["TSEND"], 0, 2);
    $htype = $ev->getValue("CALEV_TIMETYPE",0);
    for ($id=intval($dstart); $id<=intval($dend); $id++) {
      if (!is_array($tdays[$id]->events)) {
        $tdays[$id]->ecount = 0;
        $tdays[$id]->events = array();
      }
      $s = $ve["START"];  
      $e = $ve["END"];
      if ($id>$dstart) $s = 0;
      if ($id<$dend) $e = 0;
      $tdays[$id]->events[$tdays[$id]->ecount] = $ve;
      $tdays[$id]->events[$tdays[$id]->ecount]["START"] = $s;
      $tdays[$id]->events[$tdays[$id]->ecount]["END"] = $e;
      $tdays[$id]->events[$tdays[$id]->ecount]["H"] = $htype;
      $tdays[$id]->events[$tdays[$id]->ecount]["TITLE"] = $ev->getValue("CALEV_EVTITLE");
      $tdays[$id]->ecount++;
    }
  }

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
	$h->set("daylabel",$daylabel);
	if ($tdays[$cday]->ecount > 0) {
	  usort($tdays[$cday]->events, cmpEvents);
	  for ($ie=0; $ie<count($tdays[$cday]->events); $ie++) {
	    $ievent = $tdays[$cday]->events[$ie]["ID"];
	    $d[$ie]["hours"] = "";
	    if ($tdays[$cday]->events[$ie]["START"]>0) $s = substr($tdays[$cday]->events[$ie]["TSSTART"],11,5);
	    else $s = $hstart.":00";
	    if ($tdays[$cday]->events[$ie]["END"]>0) $e = substr($tdays[$cday]->events[$ie]["TSEND"],11,5);
	    else $e = $hstop.":00";
	    $d[$ie]["hours"] = $s."-".$e;

	    if ($tdays[$cday]->events[$ie]["H"]==1) $d[$ie]["hours"] = "("._("no hour").")";
	    if ($tdays[$cday]->events[$ie]["H"]==2) $d[$ie]["hours"] = "("._("all the day").")";

	    $d[$ie]["title"] = $st.$tdays[$cday]->events[$ie]["TITLE"];
	    $d[$ie]["id"] = $tdays[$cday]->events[$ie]["ID"];
	    $d[$ie]["TSSTART"] = $tdays[$cday]->events[$ie]["TSSTART"];
	    $d[$ie]["RG"] = $tdays[$cday]->events[$ie]["RG"];
	    $d[$ie]["action"] = $tdays[$cday]->events[$ie]["action"];
	  }
	}
	$h->SetBlockData("HLine", $d);
	$hday[$li]["line"] .= "<td class=\"wMonthTextTD\">".$h->gen()."</td>";
	$cday++; 
      } else {
	$hday[$li]["line"] .= "<td class=\"wMonthTextTDUnused\">&nbsp;</td>";
	if ($cday>$lastday) $alldaysdone = true;
      }
    }
    $li++;
  }
  $action->lay->SetBlockData("DLINE", $hday);
    
  return;
}

function cmpEvents($e1, $e2) {
  return $e1["START"] > $e2["START"];
}
?>
