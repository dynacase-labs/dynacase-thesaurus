<?php

include_once("FDL/Class.Doc.php");
include_once("WGCAL/Lib.WGCal.php");

function wgcal_textmonth(&$action) 
{
  $col  = 5;
  $line = 7;

  $ctime = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $firstMonthDay  = WGCalGetFirstDayOfMonth($ctime);
  $firstMonthDayN = WGCalGetFirstDayOfMonthN($firstMonthDay);
  $month = strftime("%m", $ctime);
  $year  = strftime("%Y", $ctime);
  $lastday =  WGCalDaysInMonth($ctime);

  // Search all event for this month
  $tress[] = $action->user->fid;
  $d1 = "".$year."-".$month."-01 00:00:00";
  $d2 = "".$year."-".$month."-".$lastday." 23:59:59";
  $tevents = WGCalGetAgendaEvents($action, $tress, $d1, $d2);


//   for ($li=0; $li<$line; $li++) {
//     for ($co=0; $co<=$col; $co++) {
//       $cur = $li+$co;
//     }
//   }
    
  return;
}

?>