
function explodeEvt($d1, $d2) {
  include_once("FDL/Lib.Util.php");  
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

  echo "<hr>real filtering from ".jd2cal($jd1, 'FrenchLong')." to ".jd2cal($jd2, 'FrenchLong')."<br>";
  echo "event source mode=".$e->mode." start=".jd2cal($e->jdds, 'FrenchLong')." end=".jd2cal($e->jdde, 'FrenchLong')." repeat until=".jd2cal($e->untildate, 'FrenchLong')."<br>";

  $te = $this->getTValue("evfc_excludedate");
  if (count($te)>0) {
    foreach ($te as $k => $v) if ($v!="") $e->exclude[] = StringDateToJD($v);
  }

  // return event if there are not repeatable to produce 
  if (false || $e->mode==0 || $e->untildate<$jd1 || $e->jdds>$jd2 ) {
    $eve[] = get_object_vars($this);
    return $eve;
  }

  $start = ((round($e->jdds)-0.5) < $jd1 ? $jd1 : round($e->jdds)-0.5);
  $stop = round($e->untildate)+0.4999;
  echo "filtering from ".jd2cal($start, 'FrenchLong')." to ".jd2cal($stop, 'FrenchLong')."<br>";

  switch ($e->mode) {
    
  case 1: // daily repeat
    $ref = get_object_vars($this);
    $ix = 0;
    for ($iday=$start; $iday<=$stop; $iday++) {
      if (!$this->CalEvIsExclude($e->exclude, $iday)) {
        $eve[$ix] = $ref;
        $eve[$ix]["evt_begdate"] = jd2cal($e->jdds+round($iday-$e->jdds), 'FrenchLong');
        $eve[$ix]["evt_enddate"] = $eve[$ix]["evfc_realenddate"] = jd2cal($e->jdde+round($iday-$e->jdds), 'FrenchLong');
        $eve[$ix]["evfc_repeatmode"] = 0;
        echo "evt[$ix] nbj=".round($iday-$e->jdds)." s=".$eve[$ix]["evt_begdate"]." e=".$eve[$ix]["evt_enddate"]."<br>";
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
	    $eve[$ix] = $ref;
	    $eve[$ix]["evt_begdate"] = jd2cal($e->jdds+round($iday-$e->jdds), 'FrenchLong');
	    $eve[$ix]["evt_enddate"] = $eve[$ix]["evfc_realenddate"] = jd2cal($e->jdde+round($iday-$e->jdds), 'FrenchLong');
            $eve[$ix]["evfc_repeatmode"] = 0;
	    echo "evt[$ix] nbj=".round($iday-$e->jdds)." s=".$eve[$ix]["evt_begdate"]." e=".$eve[$ix]["evt_enddate"]."<br>";
           $ix++;
          }
        }
      }
    }
    break;

  case 3: // monthly repeat
    break;

  case 4: // yearly repeat
    break;
    
  }

  return $eve;
}

function CalEvIsExclude($excl, $date) {
  foreach ($excl as $k => $v) {
    if (round($date) == round($v)) return true;
  }
  return false;
}
