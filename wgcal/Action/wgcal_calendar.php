<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: wgcal_calendar.php,v 1.17 2005/02/01 14:07:00 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage WGCAL
 */
 /**
 */


include_once("FDL/Class.Doc.php");
define("SEC_PER_DAY", 24*3600);
define("SEC_PER_HOUR", 3600);
define("SEC_PER_MIN", 60);

function GetFirstDayOfWeek($ts) {
	if ($ts<=0) return false;
	$iday  = strftime("%u",$ts);
	$dt = 1-$iday;
        $tsfwd = $ts - (($iday-1) * SEC_PER_DAY);
	$dd = strftime("%d", $tsfwd);
 	$mm = strftime("%m", $tsfwd);
 	$yy = strftime("%Y", $tsfwd);
	$fwdt = mktime ( 0, 0, 0, $mm, $dd, $yy);
	return $fwdt;
}
       	
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

function wgcal_getRessDisplayed(&$action) {
  $r = array();
  $ir = 0;
  $cals = explode("|", $action->GetParam("WGCAL_U_RESSTMPLIST", $action->GetParam("WGCAL_U_RESSDISPLAYED", $action->user->id)));
  while (list($k,$v) = each($cals)) {
    $tc = explode("%", $v);
    if ($tc[0] != "" && $tc[1] == 1) {
      $r[$ir]->id = $tc[0];
      if ($tc[0] == $action->user->fid) $r[$ir]->color = $action->GetParam("WGCAL_U_MYCOLOR", "black");
      else $r[$ir]->color = $tc[2]; 
      $ir++;
    }
  }
  return $r;
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
  if ($swe!="yes") $ndays = $dayperweek - 2;
  else $ndays = $dayperweek;
  $sdate = $action->GetParam("WGCAL_U_CALCURDATE", time());
  $pafter = $sdate + ($ndays * SEC_PER_DAY);
  $pbefore = $sdate - ($ndays * SEC_PER_DAY);
  $firstWeekDay = GetFirstDayOfWeek($sdate);
  $edate = $firstWeekDay + ($ndays * SEC_PER_DAY) - 1;
  $today = d2s("%d/%m/%Y", time());
  $curdate = d2s("%d/%m/%Y", $sdate);
  $fday  = strftime("%u",$firstWeekDay);
  // echo "start date : ".d2s($sdate)." end : ".d2s($edate)." first : ".d2s($firstWeekDay)."<br>";
  $ress = wgcal_getRessDisplayed($action);

  $year  = strftime("%Y",$sdate);
  $month = strftime("%B",$sdate);
  $week  = strftime("%V",$sdate);
  $iday  = strftime("%u",$sdate);
  $day   = strftime("%d",$sdate);

  $hstart = $action->GetParam("WGCAL_U_STARTHOUR", 8);
  $hstop  = $action->GetParam("WGCAL_U_STOPHOUR", 20);
  $hdiv   = $action->GetParam("WGCAL_U_HOURDIV", 1);

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

  $action->lay->set("F_LINE", '<td align="center" class="WGCAL_Period" colspan="'.($ndays+3).'">'
		    . N_("week").' '.$week.' - '.$month.' '.$year.'</td>');
  $action->lay->set("WEEKNUMBER", $week);
  $classalt = array ( 0 => "WGCAL_Day1", 1 => "WGCAL_Day2" );
  $curday = -1;
  $tabdays = array(); $itd=0;
  for ($i=0; $i<$ndays; $i++) { 
    $tabdays[$itd]["iday"] =  $itd;
    $tabdays[$itd++]["days"] =  strftime("%s", $firstWeekDay+($i*SEC_PER_DAY));
    $ld = strftime("%d/%m/%Y", $firstWeekDay+($i*SEC_PER_DAY));
    if (!strcmp($ld,$today)) {
      $class[$i] = $classh[$i] = "WGCAL_DayLineCur";
      $curday = $i; 
    } else if (!strcmp($ld, $curdate)) {
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
	$tcell[$itc]["urlroot"] = $urlroot;
	if ($h==($hstart-1)) $tcell[$itc]["nh"] = 1;
	else $tcell[$itc]["nh"] = 0;
	$tcell[$itc]["times"] = d2s($firstWeekDay+($id*SEC_PER_DAY)+($h*SEC_PER_HOUR)+ ($hd*$mdiv), "%s");
	$tcell[$itc]["timee"] = $tcell[$itc]["times"] + (($hd==0?1:$hd) * $mdiv) - 1;
	$tcell[$itc]["rtime"] = d2s($firstWeekDay+($id*SEC_PER_DAY), "%a %d %B %Y, ");
	$tcell[$itc]["rtime"] .= d2s($tcell[$itc]["times"],"%H:%M")." - ";
	$tcell[$itc]["rtime"] .= d2s($tcell[$itc]["timee"],"%H:%M");
	$tcell[$itc]["lref"] = "L".$nl;
	$tcell[$itc]["cref"] = "D".$id;
	$tcell[$itc]["cclass"] = $class[$id];
	$tcell[$itc]["dayclass"] = $thr[$nl]["HCLASS"];
	$tcell[$itc]["hourclass"] = $classh[$id];
	$tcell[$itc]["cellcontent"] = "";
// 	$tcell[$itc]["cellcontent"] = $h."/".$hd." ".strftime("%H:%M",$tcell[$itc]["times"])." " . strftime("%H:%M",$tcell[$itc]["timee"]);
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
  $action->lay->set("HCOUNT", (($hstop - $hstart + 1) * $hdiv ) + 1 ); // Minutes
  $action->lay->set("HSTART", ($hstart - 1)); // Minutes
  $action->lay->set("IDSTART", "D0H0");
  $action->lay->set("IDSTOP", "D".($ndays-1)."H".($nl-1));
  
  $action->lay->set("WGCAL_U_HLINETITLE", $action->GetParam("WGCAL_U_HLINETITLE", 20));
  $action->lay->set("WGCAL_U_HLINEHOURS", $action->GetParam("WGCAL_U_HLINEHOURS", 40));
  $action->lay->set("WGCAL_U_HCOLW", $action->GetParam("WGCAL_U_HCOLW", 20));
  
  $events=getAgendaEvent($action, $ress, 
			 d2s($firstWeekDay, "%Y-%m-%d %H:%M:%S"),
			 d2s($edate, "%Y-%m-%d %H:%M:%S") );
  $action->lay->SetBlockData("EVENTS", $events);
  $action->lay->SetBlockData("EVENTSSC", $events);
  $action->lay->set("comment",strftime("%x %X", time()));
}


function getAgendaEvent(&$action,$tress,$d1="",$d2="") {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $reid=getIdFromName($dbaccess,"WG_AGENDA");
  $tout=array(); 
  $it=0;
  foreach ($tress as $kr=>$vr) {
    //echo "ressource : ".$vr->id."(".$vr->color.") <br>";
    setHttpVar("idres",$vr->id);
    $dre=new Doc($dbaccess,$reid);
    $edre=$dre->getEvents($d1,$d2);
    foreach ($edre as $k=>$v) {
      $tout[]=array("REF" => $k,
		    "ID" => $v["evt_idinitiator"],
		    "ABSTRACT" => $v["evt_title"],
		    "START" => FrenchDateToUnixTs($v["evt_begdate"]),
		    "END" => FrenchDateToUnixTs($v["evt_enddate"]),
		    "COLOR" => $vr->color,
		    "SHIFT"=>0);

      $it++;
    }
  }
  //print_r2( $tout );
  return $tout;
}

?>
