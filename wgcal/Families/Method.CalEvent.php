
var $ZoneEvtAbstract =  "WGCAL:CALEV_ABSTRACT";
var $ZoneEvtCard =  "WGCAL:CALEV_CARD";


// function explodeEvt($d1, $d2) {
// }


function TexplodeEvt($d1, $d2) {
  include_once("FDL/Lib.Util.php");  
  $eve = array();
  $eve[] = $this;

  $jd1 = Iso8601ToJD($d1);
  $jd2 = Iso8601ToJD($d2);
  
  // check start and end date
  $evs = StringDateToJD($this->getValue("evt_begdate"));
  $eve = StringDateToJD($this->getValue("evt_endbegdate"));
  
  $r->mode      = $this->getValue("evfc_repeatmode");
  $r->freq      = $this->getValue("evfc_repeatfreq");
  $r->weekday   = $this->getValue("evfc_repeatweekday");
  $r->month     = $this->getValue("evfc_repeatmonth");
  $r->untildate = ($this->getValue("evfc_repeatuntil")==0 ? -1 :  StringDateToJD($this->getValue("evfc_repeatuntildate")));
  $r->exclude = array();
  $te = $this->getValue("evfc_excludedate");
  foreach ($te as $k => $v) {
    if ($v!="") $r->exclude[] = StringDateToJD($v);
  }
  
  if ($r->mode == 0) return $eve;
  if ($evs>$jd2 || ($r->untildate!=-1 && $r->untildate<$jd1)) return $eve;

  switch ($rmode) 
    {
    case 1 : $eve = r_daily($jd1, $jd2, $evs, $eve, $r); break;
    case 2 : $eve = r_weekly($jd1, $jd2, $evs, $eve, $r); break;
    case 2 : $eve = r_monthly($jd1, $jd2, $evs, $eve, $r); break;
    case 2 : $eve = r_yearly($jd1, $jd2, $evs, $eve, $r); break;
    }
  return $eve;
    
}
  

function r_daily($jd1, $jd2, $evs, $eve, $r)
{
  include_once("FDL/Lib.Util.php");  

  $rev = array();
  $rev[] = $this;

  $ate = get_object_vars($this);
  $db = ($jd1-$evs)%$r->freq;
  $ie = 0;
  for ($j = $db; $j<=$jd2; $j+$r->freq) {
    // New !!
    $rev[$ie] = $ate;
    $rev[$ie]["evt_begdate"] = jd2cal($j);
    $rev[$ie]["evt_enddate"] = jd2cal($eve-$evs);
    $rev[$ie]["evt_desc"] = $j;
  }
  return $rev;
}