<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_textmonth(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");

  $dayperline  = 7;
  $line = 5;

  $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $firstMonthDay  = WGCalGetFirstDayOfMonth($ctime);
  $firstDay = strftime("%u", $firstMonthDay);
  $firstMonthDayN = WGCalGetFirstDayOfMonthN($firstMonthDay);
  $month = strftime("%m", $ctime);
  $year  = strftime("%Y", $ctime);
  $lastday =  WGCalDaysInMonth($ctime);
  $prevmontht = $firstMonthDay-(24*3600);
  $prevmonth = strftime("%B", $prevmontht);
  $nextmontht =  (24*3600)+mktime(0,0,0, $month+1, 1, $year);
  $nextmonth = strftime("%B", $nextmontht);

  // Search all event for this month
  $tress[] = $action->user->fid;
  $d1 = "".$year."-".$month."-01 00:00:00";
  $d2 = "".$year."-".$month."-".$lastday." 23:59:59";
  $tevents = WGCalGetAgendaEvents($action, $tress, $d1, $d2);

  usort($tevents, cmpEvents);

  $tdays = array();
  foreach ($tevents as $ke => $ve) {
    $sday = strftime("%d",$ve["START"]);
    $eday = strftime("%d",$ve["END"]);
    if (!is_object($tdays[$sday])) {
      $tdays[$sday]->ecount = -1;
      $tdays[$sday]->events = array();
      $tdays[$sday]->hcode  = array();
    }
    $tdays[$sday]->ecount++;
    $tdays[$sday]->events[$tdays[$sday]->ecount] = $ve["ID"];
  }


  $start = false;
  $cday = 1;
  $action->lay->set("month",strftime("%B %Y",$ctime));
  $action->lay->set("prevmontht",$prevmontht);
  $action->lay->set("prevmonth",$prevmonth);
  $action->lay->set("nextmonth",$nextmonth);
  $action->lay->set("nextmontht",$nextmontht);
  $action->lay->set("titlespan",($dayperline-2));
  $action->lay->set("dayperline",$dayperline-1);
  for ($li=0; $li<$line; $li++) {
    $hday[$li]["line"] = "";
    for ($co=0; $co<=$dayperline-1; $co++) {
      if ($firstDay-1==$co) $start = true;
      if ($start && $cday<=$lastday) {
	$h = new Layout("WGCAL/Layout/textevent.xml", $action );
	$d = array();
	$h->set("daytitle",strftime("%A %d",($firstMonthDay+(($cday-1)*24*3600))));
	if ($tdays[$cday]->ecount == 0) {
	  //$d[0]["line"] = $d[1]["line"] = "<br>";
	} else {
	  for ($ie=0; $ie<$tdays[$cday]->ecount; $ie++) {
	    $ievent = $tdays[$cday]->events[$ie];
	    $ev = new Doc($dbaccess, $ievent);
	    $d[$ie]["hstart"] = substr($ev->getValue("CALEV_START"),11,5);
	    $d[$ie]["hend"] = substr($ev->getValue("CALEV_END"),11,5);
	    $d[$ie]["title"] = $ev->getValue("CALEV_EVTITLE");
	    $d[$ie]["id"] = $ev->id;
	  }
	}
	$h->SetBlockData("HLine", $d);
	$hday[$li]["line"] .= "<td class=\"wMonthTextTD\">".$h->gen()."</td>";
	$cday++; 
      } else {
	$hday[$li]["line"] .= "<td class=\"wMonthTextTD\"></td>";
      }
    }
  }
  $action->lay->SetBlockData("DLINE", $hday);
    
  return;
}

function cmpEvents($e1, $e2) {
  return $e1["START"] > $e2["START"];
}
?>