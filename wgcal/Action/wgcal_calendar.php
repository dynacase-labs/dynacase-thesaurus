<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.1 2004/11/26 18:52:30 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


define("SEC_PER_DAY", 24*3600);

function GetFirstDayOfWeek($ts) {
	if ($ts<=0) return false;
	$iday  = strftime("%u",$ts);
	$dt = 1-$iday;
	$fwdt = $ts - (($iday-1) * SEC_PER_DAY);
	return $fwdt;
}
       	
function wgcal_calendar(&$action) {

  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");


  $rvwidth = $action->GetParam("xxx", 45);
  $swe = $action->GetParam("WGCAL_U_VIEWWEEKEN", "yes");
  $dayperweek = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  if ($swe!="yes") $ndays = $dayperweek - 2;
  else $ndays = $dayperweek;

  $sdate = GetHttpVars("newdate", time());
  

  //$sdate = $action->Read("WGCAL_SU_CURDATE", time());
  $year  = strftime("%Y",$sdate);
  $month = strftime("%B",$sdate);
  $week  = strftime("%V",$sdate);
  $iday  = strftime("%u",$sdate);
  $day   = strftime("%d",$sdate);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv  = $action->GetParam("WGCAL_U_HOURDIV", 1);

  $today = strftime("%d/%m/%Y", time());
  $firstWeekDay = GetFirstDayOfWeek($sdate);
  $fday  = strftime("%u",$firstWeekDay);
  $action->lay->set("DIVSTART", "calareastart");
  $action->lay->set("DIVEND", "calareaend");
  
  $action->lay->set("F_LINE", '<td align="center" class="WGCAL_Period" colspan="'.($ndays+1).'">'.N_("week").' '.$week.' - '.$month.' '.$year.'</td>');
  $action->lay->set("WEEKNUMBER", $week);

  $classalt = array ( 0 => "WGCAL_Day1", 1 => "WGCAL_Day2" );
  $curday = -1;
  for ($i=0; $i<$ndays; $i++) 
    { 
      $ld = strftime("%d/%m/%Y", $firstWeekDay+($i*SEC_PER_DAY));
      if (!strcmp($ld,$today)) {
	$classh[$i] = "WGCAL_DayLineCur";
	$class[$i] = "";
        $curday = $i; 
      } else {
	$classh[$i] = "WGCAL_Period"; 
	if ($alt==1) $alt = 0;
	else $alt = 1;
	$class[$i] = $classalt[$alt];
      }
      $t[$i]["CSS"] = $classh[$i];
      $t[$i]["LABEL"] = strftime("%a %d",$firstWeekDay+($i*SEC_PER_DAY));
    }
  $action->lay->SetBlockData("DAYS_LINE", $t);
  
  for ($h=$hstart-1; $h<=($hstop * $hdiv); $h++) 
    {
      $thr[$h]["HOURR"] = ($h==($hstart-1)?"":$h);
      $thr[$h]["HCLASS"] = "WGCAL_DayHours";
      $line = "";
      for ($id=0; $id<$ndays; $id++) 
	{
	  $line .= '<td id="D'.$id.'H'.$h.'"';
	  if ($id>6) $mo = $id;
	  else $mo = $id % 7;
	  if ($id==5||$id==6) $curclass = "WGCAL_DayLineWE";  
	  else if ($curday == $id)  $curclass = "WGCAL_DayCur";
	  else $curclass = $class[$id];
	  $line .= ' class="'.$curclass.'" ';
	  $line .= " onmouseover=\"getElementById('D".$id."H".$h."').className = 'WGCAL_DayLineOver'\"";
	  $line .= " onmouseout=\"getElementById('D".$id."H".$h."').className = '".$curclass."'\"";
	  $line .= '></td>';
	}
      $thr[$h]["C_LINE"] =  $line;
    }

  $action->lay->SetBlockData("HOURS", $thr);
  $action->lay->set("DAYCOUNT", $ndays);
  $action->lay->set("HCOUNT", ($hstop - $hstart + 2)); // Minutes
  $action->lay->set("HSTART", ($hstart - 1)); // Minutes
  $action->lay->set("IDSTART", "D0H".($hstart-1));
  $action->lay->set("IDSTOP", "D".($ndays-1)."H".$hstop);
  $action->lay->set("RVWIDTH", $rvwidth);
 
  $action->lay->set("WGCAL_U_HLINETITLE", $action->GetParam("WGCAL_U_HLINETITLE", 20));
  $action->lay->set("WGCAL_U_HLINEHOURS", $action->GetParam("WGCAL_U_HLINEHOURS", 40));
  $action->lay->set("WGCAL_U_HCOLW", $action->GetParam("WGCAL_U_HCOLW", 20));
  $action->lay->set("", $action->GetParam(""));
}

?>
