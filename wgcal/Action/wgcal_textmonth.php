<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_textmonth(&$action) 
{
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal.js");

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);

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


  $tdays = array();
  foreach ($tevents as $ke => $ve) {
    $ev = new Doc($dbaccess, $ve["ID"]);
    $start = dbdate2ts($ev->getValue("CALEV_START"));
    $dstart = strftime("%d", $start);
    $end = dbdate2ts($ev->getValue("CALEV_END"));
    $dend = strftime("%d", $end);
    $title = $ev->getValue("CALEV_EVTITLE");
    for ($id=intval($dstart); $id<=intval($dend); $id++) {
      if (!is_array($tdays[$id]->events)) {
        $tdays[$id]->ecount = -1;
        $tdays[$id]->events = array();
      }
      $s = $start;  
      $e = $end;
      if ($s==$e) $s = $e = 0;
      if ($id>$dstart) $s = 0;
      if ($id<$dend) $e = 0;
      $tdays[$id]->ecount++;
      $tdays[$id]->events[$tdays[$id]->ecount]["ID"] = $ve["ID"];
      $tdays[$id]->events[$tdays[$id]->ecount]["START"] = $s;
      $tdays[$id]->events[$tdays[$id]->ecount]["RSTART"] = $ev->getValue("CALEV_START");
      $tdays[$id]->events[$tdays[$id]->ecount]["END"] = $e;
      $tdays[$id]->events[$tdays[$id]->ecount]["REND"] = $ev->getValue("CALEV_END");
      $tdays[$id]->events[$tdays[$id]->ecount]["TITLE"] = $title;
    }
  }

  $startdisplay = false;
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
      if ($firstDay-1==$co) $startdisplay = true;
      if ($startdisplay && $cday<=$lastday) {
	$h = new Layout("WGCAL/Layout/textevent.xml", $action );
	$d = array();
	$h->set("daytitle",strftime("%A %d",($firstMonthDay+(($cday-1)*24*3600))));
	if ($tdays[$cday]->ecount > 0) {
          usort($tdays[$cday]->events, cmpEvents);
	  for ($ie=0; $ie<=$tdays[$cday]->ecount; $ie++) {
	    $ievent = $tdays[$cday]->events[$ie]["ID"];
	    $ev = new Doc($dbaccess, $ievent);
	    $d[$ie]["hours"] = "";
            if ($tdays[$cday]->events[$ie]["START"]>0) $s = strftime("%H:%M",$tdays[$cday]->events[$ie]["START"]);
            else $s = $hstart."H00";
            if ($tdays[$cday]->events[$ie]["END"]>0) $e = strftime("%H:%M",$tdays[$cday]->events[$ie]["END"]);
            else $e = $hstop."H00";
	    $d[$ie]["hours"] = "[".$s."-".$e."]";
	    $d[$ie]["title"] = $st.$tdays[$cday]->events[$ie]["TITLE"];
	    $d[$ie]["id"] = $tdays[$cday]->events[$ie]["ID"];
          }
	}
	$h->SetBlockData("HLine", $d);
	$hday[$li]["line"] .= "<td class=\"wMonthTextTD\">".$h->gen()."</td>";
	$cday++; 
      } else {
	$hday[$li]["line"] .= "<td class=\"wMonthTextTDUnused\">&nbsp;</td>";
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
