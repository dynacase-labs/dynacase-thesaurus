
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.RendezVousEvent.php,v 1.6 2005/10/10 19:40:08 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package FREEDOM
 * @subpackage
 */

var $calVResume     = "WGCAL:CALEV_ABSTRACT";
var $calVCard       = "WGCAL:CALEV_CARD";
var $calVLongText   = "WGCAL:CALEV_VIEWLTEXT";
var $calVShortText  = "WGCAL:CALEV_VIEWSTEXT";

var $calPopupMenu = array( 
       "acceptrv" => array( "label" => "accept this", "app"=>"WGCAL", "action"=>"WGCAL_SETEVENTSTATE", "params" => array("st"=>2)),
       "rejectrv" => array( "label" => "reject this", "app"=>"WGCAL", "action"=>"WGCAL_SETEVENTSTATE", "params" => array("st"=>3)),
       "tbcrv" => array( "label" => "to be confirm this", "app"=>"WGCAL", "action"=>"WGCAL_SETEVENTSTATE", "params" => array("st"=>4)),
       "editrv" => array( "label" => "edit this", "app"=>"WGCAL", "action"=>"WGCAL_EDITEVENT"),
       "viewrv" => array( "label" => "view this", "app"=>"WGCAL", "action"=>"WGCAL_VIEWEVENT"),
       "deleterv" => array( "label" => "delete this", "app"=>"WGCAL", "action"=>"WGCAL_DELETEEVENT"),
       "historyrv" => array( "label"=> "delete this", "app"=>"WGCAL", "action"=>"WGCAL_HISTO")  
       );		    

function explodeEvt($d1, $d2) {
  include_once("FDL/Lib.Util.php");  
  include_once("WGCAL/Lib.wTools.php");
    
  $eve = array();

  // return event if there are not repeatable to produce 
  $ref = get_object_vars($this);
  if ($this->getValue("evfc_repeatmode")==0) {
    $eve[] = $ref;
    return $eve;
  }

  $jd1 = ($d1==""?0:Iso8601ToJD($d1));
  $jd2 = ($d2==""?5000000:Iso8601ToJD($d2));

  // check start and end date
  $e->ds = $this->getValue("evt_begdate");
  $e->de = ($this->getValue("evfc_realenddate")==""?$this->getValue("evt_enddate"):$this->getValue("evfc_realenddate"));
  $e->jdds = StringDateToJD($e->ds);
  $e->jdde = StringDateToJD($e->de);


  // really produce event ?
  $e->mode      = $this->getValue("evfc_repeatmode");
  $e->freq      = $this->getValue("evfc_repeatfreq");
  $e->weekday   = $this->getTValue("evfc_repeatweekday");
  $e->untildate = ($this->getValue("evfc_repeatuntil")==0 ? 5000001 :  StringDateToJD($this->getValue("evfc_repeatuntildate")));
  $e->untildate = ($e->untildate > $jd2 ? $jd2 : $e->untildate);
  $e->exclude = array();


  if ($e->untildate<$jd1 || $e->jdds>$jd2 ) {
    return array();
  }

  $start = ($e->jdds < $jd1 ? $jd1 : $e->jdds);
  $stop = $e->untildate;

  $hstart = substr($e->ds,11,5);
  $hend   = substr($e->de,11,5);

  $sdeb = "";

  $ix = 0;
  switch ($e->mode) {
    
  case 1: // daily repeat

    for ($iday=$start; $iday<=$stop; $iday++) {
      if (!$this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) {
	$hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart.":00";
	$he = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hend.":00";
	$jdhs = StringDateToJD($hs);
	$jdhe = StringDateToJD($he);
	if ($jdhs<$jd2 && $jdhe>$jd1) {
	  $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
	  $ix++;
	}
      }
    }
    break;

  case 2: // weekly repeat

    for ($iday=$start; $iday<=$stop; $iday++) {
      if (!$this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) {
        $cday = jdWeekDay($iday);
        foreach ($e->weekday as $kd => $vd) {
          if ($vd == $cday-1) {
	    $hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
	    $he = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hend;
	    $jdhs = StringDateToJD($hs);
	    $jdhe = StringDateToJD($he);
	    if ($jdhs<$jd2 && $jdhe>$jd1) {
	      $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
	      $ix++;
	    }
          }
        }
      }
    }
    break;

  case 3: // monthly repeat submode 0=by date 1=by day

    $refstartday = substr(jd2cal($start, 'FrenchLong'), 0, 2);
    $csmonth = substr(jd2cal($start, 'FrenchLong'), 3, 2);
    $csyear = substr(jd2cal($start, 'FrenchLong'), 6, 4);

    $refendday = substr(jd2cal($stop, 'FrenchLong'), 0, 2);
    $cemonth = substr(jd2cal($stop, 'FrenchLong'), 3, 2);
    $ceyear = substr(jd2cal($stop, 'FrenchLong'), 6, 4);

    $sdate = $this->getValue("evt_begdate"); 
//     $csmonth = substr($sdate, 3, 2);
//     $csyear = substr($sdate, 6, 4);

    $rs_ho = substr($e->ds, 11, 2);
    $rs_mi = substr($e->ds, 14, 2);
    $rs_da = substr($e->ds, 0, 2);
    $rs_mo = substr($e->ds, 3, 2);
    $rs_ye = substr($e->ds, 6, 4);
    if ($rs_da<$refstartday) $csmonth++;
    if ($csmonth>12) { $csmonth=1; $csyear++; }
    $rs = cal2jd("", $csyear, $csmonth, $rs_da, $rs_ho, $rs_mi, 0);

    $edate = ($this->getValue("evfc_realenddate")==""?$this->getValue("evt_enddate"):$this->getValue("evfc_realenddate")) ; //jd2cal($stop, 'FrenchLong');
    $re_ho = substr($e->de, 11, 2);
    $re_mi = substr($e->de, 14, 2);
    $re_da = substr($e->de, 0, 2);
    $re_mo = substr($e->de, 3, 2);
    $re_ye = substr($e->de, 6, 4);
    if ($re_da<$refstopday) $cemonth++;
    if ($cemonth>12) { $cemonth = 0; $ceyear++; }
    $re = cal2jd("", $csyear, $csmonth, $re_da, $re_ho, $re_mi, 0);

    if ($this->getValue("evfc_repeatmonth")!=1) {
//      echo "D: $rs $rs_da/$csmonth/$csyear $rs_ho:$rs_mi<br>"; 
//      echo "E: $re $re_da/$cemonth/$ceyear $re_ho:$re_mi<br>"; 
//      echo "Rd: $start ".jd2cal($start, 'FrenchLong')."<br>";
//      echo "Re: $stop  ".jd2cal($stop, 'FrenchLong')."<br>";
      if ($rs<=$stop && $re>=$start) {
	$nstart = jd2cal($rs, 'FrenchLong');
	$nend  = jd2cal($re, 'FrenchLong');
 	if (!$this->CalEvIsExclude($nstart)) {
	  $eve[$ix] = $this->CalEvDupEvent($ref, $nstart, $nend);
	  $ix++;
 	}
      }

    } else {
      

//       $idate = jd2cal($start, 'FrenchLong');
//       $itsdate = w_dbdate2ts($idate);

//       $d = substr($e->ds,0,2);
//       $P = floor($d / 7);
//       $J = strftime("%w", $itsdate); 

//       $mD = strftime("%w", "01".$rsdate);
//       $dt = ($J - $mD);
//       if ($dt<0) $dt = ($mD + $J);

//       $cd = ($P * 7) - $dt;
      
//       echo "Date = ($idate) P=$P J=$J  mD=$mD dt=$dt   newday = $cd".$rsdate."<br>";

    }
    break;

  case 4: // yearly repeat
    $cyear = substr(jd2cal($start, 'FrenchLong'),6,4);
    $rday = substr($e->ds,0,6) . $cyear . substr($e->ds,10,6);
    $jdrday = StringDateToJD($rday);
    if ($jdrday>=$start && $jdrday<=$stop) {
      $hs = substr($rday,0,10)." ".$hstart;
      $he = substr($rday,0,10)." ".$hend;
      $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
      $ix++;
    }
    break;
    
  }
//   AddWarningMsg($sdeb);
  return $eve;
}

function CalEvIsExclude($date) {
  $te = $this->getTValue("evfc_excludedate");
  if (count($te)>0) {
    foreach ($te as $k => $v) {
      if ($v=="") continue;
      if (substr($v, 0, 10) == substr($date, 0, 10)) return true;
    }
  }
  return false;
}

function CalEvDupEvent($ref, $start, $end) {
  include_once("WGCAL/Lib.wTools.php");
  $e = $ref;
  $e["evt_begdate"] = $start;
  $e["evt_enddate"] = $e["evfc_realenddate"] = $end;
  if ($ref["evfc_evalarm"]==1) {
    $htime = w_dbdate2ts($e["evt_begdate"]);
    $hd = ($e["evfc_alarmd"] * 3600 * 24)  + ($e["evfc_alarmh"] * 3600) + ($e["evfc_alarmm"] * 60);
    $e["evfc_alarmtime"] = w_datets2db($htime - $hd);
  }
  return $e;
}




function JDRoundDay($jd) {
  return(floor($jd+0.5));
}
