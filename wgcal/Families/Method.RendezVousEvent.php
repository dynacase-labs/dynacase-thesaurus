
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.RendezVousEvent.php,v 1.4 2005/09/21 16:44:31 marc Exp $
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
  $e->month     = $this->getValue("evfc_repeatmonth");
  $e->untildate = ($this->getValue("evfc_repeatuntil")==0 ? 5000001 :  StringDateToJD($this->getValue("evfc_repeatuntildate")));
  $e->untildate = ($e->untildate > $jd2 ? $jd2 : $e->untildate);
  $e->exclude = array();


  $te = $this->getTValue("evfc_excludedate");
  if (count($te)>0) {
    foreach ($te as $k => $v) if ($v!="") $e->exclude[] = StringDateToJD($v);
  }

  // return event if there are not repeatable to produce 
  $ref = get_object_vars($this);
  
  if ($e->mode==0) {
    $eve[] = $ref;
    return $eve;
  }
  if ($e->untildate<$jd1 || $e->jdds>$jd2 ) {
    return array();
  }

  $start = ($e->jdds < $jd1 ? $jd1 : $e->jdds);
  $start = $this->JDRoundDay($start) - 0.5;

  $stop = $this->JDRoundDay($e->untildate)+0.4999;
//   echo "filtering from ".jd2cal($start, 'FrenchLong')." to ".jd2cal($stop, 'FrenchLong')."<br>";

  $hstart = substr($e->ds,11,5);
  $hend   = substr($e->de,11,5);

  $sdeb = "";

  switch ($e->mode) {
    
  case 1: // daily repeat
    $ix = 0;
    for ($iday=$start; $iday<=$stop; $iday++) {
      if (!$this->CalEvIsExclude($e->exclude, $iday)) {
	$hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
	$he = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hend;
	$eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
        $ix++;
      }
    }
    break;

  case 2: // weekly repeat
    $ref = get_object_vars($this);
    $ix = 0;
    for ($iday=$start; $iday<=$stop; $iday++) {
      if (!$this->CalEvIsExclude($e->exclude, $iday)) {
        $cday = jdWeekDay($iday);
        foreach ($e->weekday as $kd => $vd) {
          if ($vd == $cday-1) {
	    $hs = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hstart;
	    $he = substr(jd2cal($iday, 'FrenchLong'),0,10)." ".$hend;
	    $eve[$ix] = $this->CalEvDupEvent($ref, $hs, $he);
	    $ix++;
          }
        }
      }
    }
    break;

  case 3: // monthly repeat submode 0=by date 1=by day

    $sdate = jd2cal($start, 'FrenchLong');
    $dsdate = substr($sdate, 0, 2);
    $rsdate = substr($sdate, 2);
    $s_lmd = w_DaysInMonth(w_dbdate2ts($sdate));
    
    $edate = jd2cal($stop, 'FrenchLong');
    $dedate = substr($edate, 0, 2);
    $redate = substr($sdate, 2);
    
    if ($e->month==0) {

      $csday = substr($e->ds,0,2);
      $ceday = substr($e->de,0,2);

      if ($csday>=$dsdate &&  $csday<=$s_lmd) {
	$nstart = $csday.$rsdate;
	$nend  = $csday.$redate;
	$eve[$ix] = $this->CalEvDupEvent($ref, $nstart, $nend);
	$ix++;
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

function CalEvIsExclude($excl, $date) {
  foreach ($excl as $k => $v) {
    if (round($date) == round($v)) return true;
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
