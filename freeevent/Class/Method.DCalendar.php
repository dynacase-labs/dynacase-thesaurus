<?php

var $cviews=array("FREEEVENT:PLANNER");
function getEvents($d1="",$d2="") {

  return $this->getContent();
}


function planner($target="finfo",$ulink=true,$abstract="Y") {
  include_once("FREEEVENT/Lib.DCalendar.php");
  include_once("FDL/Lib.Color.php");
  global $action;
  $tevt=$this->getEvents();

  $mb=microtime();
 
  // search ressources
  $dstart=toDate(getv($tevt[0],"evt_begdate"));
  $dend=toDate(getv($tevt[0],"evt_enddate"));
  
  $mstart=DatetoMinute($dstart);
  $mend=DatetoMinute($dend);
  $wdate=new Date();
  foreach ($tevt as $k=>$v) {
    // toIso8601(getv($v,"evt_begdate"));
    //toIso8601(getv($v,"evt_enddate"));
    $wdate->setDate(toIso8601(getv($v,"evt_begdate")));
    $mdate=DatetoMinute($wdate);

    if ($mstart > $mdate) $mstart=$mdate;
    $tevt[$k]["m1"]=$mdate;
    $wdate->setDate(toIso8601(getv($v,"evt_enddate")));
    
    $mdate=DatetoMinute($wdate);

    if ($mend < $mdate) $mend=$mdate;
    $tevt[$k]["m2"]=$mdate;
  }
  uasort($tevt,"cmpevtm1");
  $ridx=0;
  $delta=$mend-$mstart;
  $sub=1;
  $idc=0;
  print "delta=$delta";
  print " - <B>".microtime_diff(microtime(),$mb)."</B> ";
  foreach ($tevt as $k=>$v) {
    $tr=$this->_val2array(getv($v,"evt_idres"));
    $x=floor(100*($v["m1"]-$mstart)/$delta);
    $w=floor(100*($v["m2"]-$v["m1"])/$delta);
    foreach ($tr as $ki=>$ir) {
      if (! isset($colorredid[$ir])) $colorredid[$ir]=$idc++;
      $sub++;
      $RN[$ir][]=array("w"=>sprintf("%d",($w<1)?1:$w),
		       "absx"=>$v["m1"],
		       "absw"=>$v["m2"]-$v["m1"],
		       "line"=>$k,
		       //		       "subline"=>$sub++,
		       "subline"=>$colorredid[$ir],
		       "divid"=>"div$k$ki",
		       "idx"=>$sub-2,
		       "rid"=>getv($v,"evt_idinitiator"),
		       "eid"=>getv($v,"id"),
		       "bartitle"=>sprintf("[%s] %s - %s",$v["title"],getv($v,"evt_begdate"),getv($v,"evt_enddate")));
      $SX[$ir]+=$w;
      $tres[$ir]=array("BAR"=>"bar$ir",
		       "res"=>getv($v,"evt_res"));
      
    }
    
  }
  
  $dcol=360/count($tres);
  foreach ($tres as $k=>$v) {    
    
    //  $rn=1;
    $col=HSL2RGB($colorredid[$k]*$dcol,1,0.5);
    foreach ($RN[$k] as $kn=>$vn) $RN[$k][$kn]["color"]=$col;
    $tres[$k]["rescolor"]=$col;
    $this->lay->setBlockData("bar$k",$RN[$k]);
  }
  $this->lay->setBlockData("RES",$tres);
  $this->lay->set("barimg",$action->GetImageUrl('baqua.png'));
  $this->lay->set("begdate",MinutetoDate($mstart));
  $this->lay->set("enddate",MinutetoDate($mend));
  $this->lay->set("mstart",$mstart);
  $this->lay->set("mend",$mend);

  print "<HR>". print " - <B>".microtime_diff(microtime(),$mb)."</B>";
  print "<hr>";

}

?>