<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.7 2004/12/17 15:46:25 marc Exp $
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
       	
function printhdiv($hdiv, $hd) {
  $sh = "00";
  $sh = sprintf("%d",(60/$hdiv)*$hd);
  if (strlen($sh) == 1) $sh = "0".$sh;
  return $sh;
}


function wgcal_getRessDisplayed(&$action) {
  $r = array();
  $ir = 0;
  $cals = explode("|", $action->GetParam("WGCAL_U_RESSDISPLAYED", ""));
  while (list($k,$v) = each($cals)) {
    $tc = explode("%", $v);
    if ($tc[0] != "" && $tc[1] == 1) {
      $r[$ir]->id = $tc[0];
      $r[$ir]->color = $tc[2]; 
      $ir++;
    }
  }
  return $r;
}
  
function GetRegisterDate(&$action) {
  return $action->Read("WGCAL_CUR_TIME", time());
}

function SetRegisterDate(&$action, $d) {
   $action->Register("WGCAL_CUR_TIME", $d);
 
}

function wgcal_calendar(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef("WHAT/Layout/DHTMLapi.js");
  $action->parent->AddJsRef("WHAT/Layout/AnchorPosition.js");
  $action->parent->AddJsRef("WGCAL/Layout/wgcal_calendar.js");


  $rvwidth = $action->GetParam("xxx", 45);
  $swe = $action->GetParam("WGCAL_U_VIEWWEEKEN", "yes");
  $dayperweek = $action->GetParam("WGCAL_U_DAYSVIEWED", 7);
  if ($swe!="yes") $ndays = $dayperweek - 2;
  else $ndays = $dayperweek;

  $sdate = GetHttpVars("newdate", time());
  if ($sdate == 0) {
    $sdate = GetRegisterDate($action);
  }
  SetRegisterDate($action, $sdate);
  $sdatef = strftime("%d/%m/%Y", $sdate);
  
  $ress = wgcal_getRessDisplayed($action);
  //echo "Ressources : ";   foreach ($ress as $k => $v) echo $v->id." (".$v->color.") |";

  $year  = strftime("%Y",$sdate);
  $month = strftime("%B",$sdate);
  $week  = strftime("%V",$sdate);
  $iday  = strftime("%u",$sdate);
  $day   = strftime("%d",$sdate);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv  = $action->GetParam("WGCAL_U_HOURDIV", 1);

  if ($hdiv>1) $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40) / ($hdiv - 1);
  else $hhight = $action->GetParam("WGCAL_U_HLINEHOURS",40);
  
  $today = strftime("%d/%m/%Y", time());
  $firstWeekDay = GetFirstDayOfWeek($sdate);
  $fday  = strftime("%u",$firstWeekDay);

  $action->lay->set("DIVSTART", "calareastart");
  $action->lay->set("DIVEND", "calareaend");
  
  $action->lay->set("F_LINE", '<td align="center" class="WGCAL_Period" colspan="'.($ndays+1).'">'
		    . N_("week").' '.$week.' - '.$month.' '.$year.'</td>');
  $action->lay->set("WEEKNUMBER", $week);
  $classalt = array ( 0 => "WGCAL_Day1", 1 => "WGCAL_Day2" );
  $curday = -1;
  for ($i=0; $i<$ndays; $i++) 
    { 
      $ld = strftime("%d/%m/%Y", $firstWeekDay+($i*SEC_PER_DAY));
      if (!strcmp($ld,$today)) {
	$class[$i] = $classh[$i] = "WGCAL_DayLineCur";
        $curday = $i; 
      } else if (!strcmp($ld,$sdatef)) {
	$classh[$i] = "WGCAL_DayLineCur";
	$class[$i] = "WGCAL_DaySelected";
      } else {
	$classh[$i] = "WGCAL_Period"; 
	$classh[$i] = "WGCAL_Period"; 
	if ($i==5||$i==6) $class[$i] = "WGCAL_DayLineWE";  
	else {
	if ($alt==1) $alt = 0;
	else $alt = 1;
	$class[$i] = $classalt[$alt];
}
      }
      $t[$i]["IDD"] = $i;
      $t[$i]["CSS"] = $classh[$i];
      $t[$i]["LABEL"] = strftime("%a %d",$firstWeekDay+($i*SEC_PER_DAY));
    }
  $action->lay->SetBlockData("DAYS_LINE", $t);


  $lcell = new Layout( "WGCAL/Layout/wgcal-cellcalendar.xml", $action );
  $nl = 0;
  for ($h=$hstart-1; $h<=$hstop; $h++) 
    {
      for ($hd=0; $hd<$hdiv&&!($hd>0&&$h==$hstart-1); $hd++) 
        {
          $thr[$nl]["LID"] = $nl;
          $thr[$nl]["HLINEHOURS"] = $hhight;
          $thr[$nl]["HCLASS"] = "WGCAL_DayNoHours";
	  if ($h==($hstart-1)) $thr[$nl]["HOURR"] = "";
	  else {
            if ($hd==0) {
		$thr[$nl]["HOURR"] = ($h==($hstart-1)?"":$h)."H00";
          	$thr[$nl]["HCLASS"] = "WGCAL_DayHours";
    	    } else {
	        $thr[$nl]["HOURR"] = ($h==($hstart-1)?"":$h)."H".printhdiv($hdiv,$hd);
                $thr[$nl]["HCLASS"] = "WGCAL_DayMin";
	    }
	  }
	  $tcell = array();
	  $itc = 0;
          for ($id=0; $id<$ndays; $id++) 
	    {
	      if ($id>6) $mo = $id;
	      else $mo = $id % 7;
              $tcell[$itc]["cellref"] = 'D'.$id.'H'.$nl;
              $tcell[$itc]["urlroot"] = $action->GetParam("CORE_STANDURL");
              $tcell[$itc]["time"] = strftime("%s",($firstWeekDay+($id*SEC_PER_DAY))+($h*SEC_PER_HOUR));
              $tcell[$itc]["rtime"] = strftime("%a %d",$firstWeekDay+($id*SEC_PER_DAY)).', '.$h.'H'.printhdiv($hdiv,$hd).'"';
	      $tcell[$itc]["lref"] = "L".$nl;
	      $tcell[$itc]["cref"] = "D".$id;
	      $tcell[$itc]["cclass"] = $class[$id];
	      $tcell[$itc]["dayclass"] = $thr[$nl]["HCLASS"];
	      $tcell[$itc]["hourclass"] = $classh[$id];
              $itc++;
	    }
          $lcell->SetBlockData("CELLS", $tcell);
          $thr[$nl]["C_LINE"] =  $lcell->Gen();
	  $nl++;
        }
    }
  $action->lay->SetBlockData("HOURS", $thr);

  $action->lay->set("DAYCOUNT", $ndays);
  $action->lay->set("HCOUNT", ($hstop - $hstart + 2)); // Minutes
  $action->lay->set("HSTART", ($hstart - 1)); // Minutes
  $action->lay->set("IDSTART", "D0H".($hstart-1));
  $action->lay->set("IDSTOP", "D".($ndays-1)."H".($nl-1));
  $action->lay->set("RVWIDTH", $rvwidth);
 
  $action->lay->set("WGCAL_U_HLINETITLE", $action->GetParam("WGCAL_U_HLINETITLE", 20));
  $action->lay->set("WGCAL_U_HLINEHOURS", $action->GetParam("WGCAL_U_HLINEHOURS", 40));
  $action->lay->set("WGCAL_U_HCOLW", $action->GetParam("WGCAL_U_HCOLW", 20));
}

?>
