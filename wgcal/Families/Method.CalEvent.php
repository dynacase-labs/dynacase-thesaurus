
// function explodeEvt($d1, $d2) {
//   echo "explodeEvt($d1, $d2)<br>";
//   print_r2($this);
//   return $this;
// }


function TexplodeEvt($d1, $d2) {
  include_once("FDL/Lib.Util.php");  
  $eve = array();

  $jd1 = ($d1==""?0:Iso8601ToJD($d1));
  $jd2 = ($d2==""?5000000:Iso8601ToJD($d2));
  
  // check start and end date
  $e->ds = StringDateToJD($this->getValue("evt_begdate"));
  $e->de = StringDateToJD($this->getValue("evt_endbegdate"));
  $e->dur = $e->de - $e->ds;

  // really produce event ?
  $e->mode      = $this->getValue("evfc_repeatmode");
  $e->freq      = $this->getValue("evfc_repeatfreq");
  $e->weekday   = $this->getValue("evfc_repeatweekday");
  $e->month     = $this->getValue("evfc_repeatmonth");
  $e->untildate = ($this->getValue("evfc_repeatuntil")==0 ? -1 :  StringDateToJD($this->getValue("evfc_repeatuntildate")));
  $e->exclude = array();
  $te = $this->getValue("evfc_excludedate");
  foreach ($te as $k => $v) {
    if ($v!="") $e->exclude[] = StringDateToJD($v);
  }
  
  // return event if there are not repeatable to produce 
  if (($r->de<$jd1 && $r->d1>$jd2) || ($r->de<$jd1) || ($r->mode==0)) return $eve;


  switch ($e->mode) {
    
  case 1: // daily repeat
    $ref = get_object_vars($this);
    $ix = 0;
    for ($iday=$jd1; $iday<=$jd2; $iday++) {
      $eve[$ix] = $ref;
      $eve[$ix]["evt_begdate"] = jd2cal($iday);
      $eve[$ix]["evt_endbegdate"] = jd2cal($iday) + $e->dur;
      $eve[$ix]["evfc_repeatmode"] = 0;
      $ix++;
    }
    break;


  case 2: // weekly repeat
    break;

  case 3: // monthly repeat
    break;

  case 4: // yearly repeat
    break;
    
  }


  return $eve;
}