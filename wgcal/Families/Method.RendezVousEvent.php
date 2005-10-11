
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.RendezVousEvent.php,v 1.7 2005/10/11 15:53:56 marc Exp $
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

  $sdeb = "";

  $jd1 = ($d1==""?0:Iso8601ToJD($d1));
  $jd2 = ($d2==""?5000000:Iso8601ToJD($d2));
  $sdeb .= "Periode [".$this->__trcJdDate($jd1).":".$this->__trcJdDate($jd2)."\n";

  $jdDateStart = StringDateToJD($this->getValue("evt_begdate"));
  $jdDateEnd   = StringDateToJD($this->getValue("evfc_realenddate"));
  $jdDuration = $jdDateEnd - $jdDateStart;
  $jdREndDate  = StringDateToJD($this->getValue("evt_enddate"));
  $sdeb .= "Event Start:".$this->__trcJdDate($jdDateStart)." End:".$this->__trcJdDate($jdDateEnd)." RealEndDate:".$this->__trcJdDate($jdREndDate)." Dur".$this->__trcJdDate($jdDuration)."\n";

  if ($this->getValue("evfc_repeatmode")==0 || $jdREndDate<$jd1 || $jdDateStart>$jd2 ) {
    return array();
  }

  // $freq      = $this->getValue("evfc_repeatfreq");


  $ix = 0;
  switch ($this->getValue("evfc_repeatmode")) {
    
  case 1: // daily repeat

    $start = ($jdDateStart>$jd1 ? $jdDateStart : $jd1);
    $stop = ($jdREndDate<$jd2 ?  $jdREndDate : $jd2);
    $hstart = substr($this->getValue("evt_begdate"), 11, 5);
  
    for ($iday=$start; $iday<=$stop; $iday++) {
      if (!$this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) {
	$hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
	$jdhs = StringDateToJD($hs);
	$he = jd2cal(($jdhs+$jdDuration), 'FrenchLong');
	if ($jdhs<$jd2 && ($jdhs+$jdDuration)>$jd1) {
	  $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
	  $ix++;
	}
      }
    }
    break;

  case 2: // weekly repeat

    
    $start = ($jdDateStart>$jd1 ? $jdDateStart : $jd1);
    $stop = ($jdREndDate<$jd2 ?  $jdREndDate : $jd2);
    $hstart = substr($this->getValue("evt_begdate"), 11, 5);


    for ($iday=$start; $iday<=$stop; $iday++) {
      if ($this->CalEvIsExclude(jd2cal($iday, 'FrenchLong'))) continue;
      $cday = jdWeekDay($iday);
      $hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
      $jdhs = StringDateToJD($hs);
      $he = jd2cal(($jdhs+$jdDuration), 'FrenchLong');
      $jStart = jdWeekDay($jdhs);
      $jStop  = jdWeekDay(($jdhs+$jdDuration));
      $thisDay = false;

      foreach ($this->getTValue("evfc_repeatweekday") as $kd => $vd) {
        if ($vd == ($jStart - 1)) $thisDay = true;
      }
      if (!$thisDay) continue;
      if ($cday!=$jStart && $cday!=$jStop) continue;

     if ($jdhs<$jd2 && ($jdhs+$jdDuration)>$jd1) {
        $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
     }
    }
    break;




  case 3: // monthly repeat submode 0=by date 1=by day

    $start = ($jdDateStart>$jd1 ? $jdDateStart : $jd1);
    $stop = ($jdREndDate<$jd2 ?  $jdREndDate : $jd2);

//     $sdeb .= "Date Debut:".$this->__trcJdDate($start).". Fin:".$this->__trcJdDate($stop)."\n";

    $refstartday = substr(jd2cal($start, 'FrenchLong'), 0, 2);
    $csmonth = substr(jd2cal($start, 'FrenchLong'), 3, 2);
    $csyear = substr(jd2cal($start, 'FrenchLong'), 6, 4);

    $refendday = substr(jd2cal($stop, 'FrenchLong'), 0, 2);
    $cemonth = substr(jd2cal($stop, 'FrenchLong'), 3, 2);
    $ceyear = substr(jd2cal($stop, 'FrenchLong'), 6, 4);

    $ds = $this->getValue("evt_begdate");
    $rs_ho = substr($ds, 11, 2);
    $rs_mi = substr($ds, 14, 2);
    $rs_da = substr($ds, 0, 2);
    $rs_mo = substr($ds, 3, 2);
    $rs_ye = substr($ds, 6, 4);
    if ($rs_da<$refstartday) $csmonth++;
    if ($csmonth>12) { $csmonth=1; $csyear++; }
    $rs = cal2jd("CE", $csyear, $csmonth, $rs_da, $rs_ho, $rs_mi, 0);

    $re = $rs + $jdDuration;

//     $sdeb .= "A PRODUIRE ? : [".$this->__trcJdDate($rs).":".$this->__trcJdDate($re)."]\n";
    if ($this->getValue("evfc_repeatmonth")!=1) {
      if ($rs<=$stop && $re>=$start) {
	$nstart = jd2cal($rs, 'FrenchLong');
	$nend  = jd2cal($re, 'FrenchLong');
 	if (!$this->CalEvIsExclude($nstart)) {
//           $sdeb .= "Evt prod : [".$this->__trcJdDate($jdhs).":".$this->__trcJdDate($jdhs+$jdDuration)."]\n";
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
    $ds = $this->getValue("evt_begdate");
    $start = ($jdDateStart>$jd1 ? $jdDateStart : $jd1);
    $stop = ($jdREndDate<$jd2 ?  $jdREndDate : $jd2);
    $cyear = substr(jd2cal($start, 'FrenchLong'),6,4);
    $rday = substr($ds,0,6) . $cyear . substr($ds,10,6);
    $jdrday = StringDateToJD($rday);
    if ($jdrday>=$start && $jdrday<=$stop) {
      $hs = substr($rday,0,10)." ".$hstart;
      $jdhs = StringDateToJD($hs);
      $he = jd2cal($jdhs + $jdDuration, 'FrenchLong');
      $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
      $ix++;
    }
    $cnyear = substr(jd2cal($stop, 'FrenchLong'),6,4);
    if ($cnyear!=$cyear) {
      $rday = substr($ds,0,6) . $cnyear . substr($ds,10,6);
      $jdrday = StringDateToJD($rday);
      if ($jdrday>=$start && $jdrday<=$stop) {
        $hs = substr($rday,0,10)." ".$hstart;
        $jdhs = StringDateToJD($hs);
         $he = jd2cal($jdhs + $jdDuration, 'FrenchLong');
        $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
        $ix++;
      }
    }
    break;
    
  }
//    AddWarningMsg($sdeb);
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




function __trcJdDate($jd) {
  return "($jd) ".jd2cal($jd, 'FrenchLong');
}

function getJs2DateField($fdate="") {
  // Db 30/08/2005 11:00:00 CEST
  if (preg_match("/^(\d\d)\/(\d\d)\/(\d\d\d\d)\s?(\d\d)?:?(\d\d)?:?(\d\d)?\s?(\w+)?$/", $fdate,$r)) {
    return $r;
  }
  return false;
}